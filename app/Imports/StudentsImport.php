<?php

namespace App\Imports;

use App\Models\Program;
use App\Models\Student;
use App\Support\MiddleInitial;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    /** @var Collection<string, string> lowercase program_name => program_code */
    protected Collection $programsByName;

    /** @var Collection<string, string> uppercase program_code => program_code */
    protected Collection $programsByCode;

    public function __construct()
    {
        $programs = Program::query()
            ->get(['program_code', 'program_name']);

        $this->programsByCode = $programs
            ->mapWithKeys(fn (Program $p) => [mb_strtoupper(trim((string) $p->program_code)) => $p->program_code]);

        $this->programsByName = $programs
            ->mapWithKeys(fn (Program $p) => [mb_strtolower(trim((string) $p->program_name)) => $p->program_code]);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $data = $this->mapRow($row instanceof Collection ? $row->toArray() : (array) $row);

            if ($data === null) {
                $this->skipped++;

                continue;
            }

            $idNumber = $data['id_number'];
            $existing = $idNumber
                ? Student::query()->where('id_number', $idNumber)->first()
                : null;

            if ($existing) {
                // Do not overwrite a non-empty QR with empty unless the sheet provides one.
                if (empty($data['qrcode'])) {
                    unset($data['qrcode']);
                }

                $existing->fill($data);
                $existing->save();
                $this->updated++;
            } else {
                if (empty($data['qrcode'])) {
                    $data['qrcode'] = $this->generateNextQrCode();
                }

                Student::create($data);
                $this->created++;
            }
        }
    }

    /**
     * @param  array<string|int, mixed>  $row
     * @return array<string, mixed>|null
     */
    protected function mapRow(array $row): ?array
    {
        $row = $this->normalizeKeys($row);

        // HSC-STUDENTS.xlsx: Name, Program, ID, Value Of QR Code, Contact #, Address, Guardian
        if ($this->hasAnyKey($row, ['name', 'value_of_qr_code', 'guardian'])) {
            return $this->mapHscRow($row);
        }

        // App export / legacy import columns
        if ($this->hasAnyKey($row, ['lastname', 'last_name', 'firstname', 'first_name', 'id_number'])) {
            return $this->mapLegacyRow($row);
        }

        // Positional columns (no usable headers)
        if (array_key_exists(0, $row) || array_key_exists(1, $row)) {
            return $this->mapPositionalRow($row);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    protected function mapHscRow(array $row): ?array
    {
        $name = trim((string) ($row['name'] ?? ''));
        $idNumber = $this->normalizeIdNumber($row['id'] ?? $row['id_number'] ?? null);

        if ($name === '' && $idNumber === null) {
            return null;
        }

        [$firstname, $lastname, $middleInitial] = $this->parseFullName($name);

        if ($firstname === '' && $lastname === '') {
            return null;
        }

        $qrcode = $this->stringOrNull($row['value_of_qr_code'] ?? $row['qrcode'] ?? $row['qr_code'] ?? null);
        $program = $this->stringOrNull($row['program'] ?? $row['course'] ?? null);
        $mobile = $this->normalizeMobile($row['contact'] ?? $row['contact_'] ?? $row['mobile_number'] ?? null);
        $address = $this->stringOrNull($row['address'] ?? null);
        $guardian = $this->stringOrNull($row['guardian'] ?? $row['emergency_person'] ?? null);

        return [
            'id_number' => $idNumber,
            'firstname' => $firstname !== '' ? $firstname : ($lastname ?: 'Unknown'),
            'lastname' => $lastname !== '' ? $lastname : ($firstname ?: 'Unknown'),
            'middle_initial' => MiddleInitial::normalize($middleInitial),
            'qrcode' => $qrcode,
            'course' => $this->resolveCourse($program),
            'mobile_number' => $mobile,
            'address' => $address,
            'emergency_person' => $guardian,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    protected function mapLegacyRow(array $row): ?array
    {
        $lastname = trim((string) ($row['lastname'] ?? $row['last_name'] ?? ''));
        $firstname = trim((string) ($row['firstname'] ?? $row['first_name'] ?? ''));
        $idNumber = $this->normalizeIdNumber($row['id_number'] ?? $row['id'] ?? null);

        if ($lastname === '' && $firstname === '' && $idNumber === null) {
            return null;
        }

        return [
            'id_number' => $idNumber,
            'lastname' => $lastname !== '' ? $lastname : 'Unknown',
            'firstname' => $firstname !== '' ? $firstname : 'Unknown',
            'middle_initial' => MiddleInitial::normalize($row['middle_initial'] ?? $row['middle_name'] ?? null),
            'birthday' => $this->stringOrNull($row['birthday'] ?? null),
            'qrcode' => $this->stringOrNull($row['qrcode'] ?? $row['qr_code'] ?? null),
            'course' => $this->resolveCourse($this->stringOrNull($row['course'] ?? $row['program'] ?? null)),
            'year' => $this->stringOrNull($row['year'] ?? null),
            'mobile_number' => $this->normalizeMobile($row['mobile_number'] ?? $row['contact'] ?? null),
            'address' => $this->stringOrNull($row['address'] ?? null),
            'emergency_person' => $this->stringOrNull($row['emergency_person'] ?? $row['guardian'] ?? null),
            'emergency_relationship' => $this->stringOrNull($row['emergency_relationship'] ?? null),
            'emergency_number' => $this->normalizeMobile($row['emergency_number'] ?? null),
            'emergency_address' => $this->stringOrNull($row['emergency_address'] ?? null),
        ];
    }

    /**
     * Legacy positional layout:
     * id_number, lastname, firstname, middle_initial, birthday, qrcode, course, year, mobile, address
     *
     * @param  array<int|string, mixed>  $row
     * @return array<string, mixed>|null
     */
    protected function mapPositionalRow(array $row): ?array
    {
        $values = array_values($row);
        $idNumber = $this->normalizeIdNumber($values[0] ?? null);
        $lastname = trim((string) ($values[1] ?? ''));
        $firstname = trim((string) ($values[2] ?? ''));

        // Skip header-like first data if headers somehow leak into positional mode
        if (mb_strtolower($lastname) === 'lastname' || mb_strtolower((string) ($values[0] ?? '')) === 'id_number') {
            return null;
        }

        if ($lastname === '' && $firstname === '' && $idNumber === null) {
            return null;
        }

        return [
            'id_number' => $idNumber,
            'lastname' => $lastname !== '' ? $lastname : 'Unknown',
            'firstname' => $firstname !== '' ? $firstname : 'Unknown',
            'middle_initial' => MiddleInitial::normalize($values[3] ?? null),
            'birthday' => $this->stringOrNull($values[4] ?? null),
            'qrcode' => $this->stringOrNull($values[5] ?? null),
            'course' => $this->resolveCourse($this->stringOrNull($values[6] ?? null)),
            'year' => $this->stringOrNull($values[7] ?? null),
            'mobile_number' => $this->normalizeMobile($values[8] ?? null),
            'address' => $this->stringOrNull($values[9] ?? null),
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: ?string} firstname, lastname, middle_initial
     */
    protected function parseFullName(string $name): array
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
        if ($name === '') {
            return ['', '', null];
        }

        // "O.verdeflor" → "O. verdeflor"
        $name = preg_replace('/\b([A-Za-z])\.([A-Za-z])/u', '$1. $2', $name) ?? $name;

        $parts = preg_split('/\s+/u', $name) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

        if ($parts === []) {
            return ['', '', null];
        }

        if (count($parts) === 1) {
            return [$parts[0], $parts[0], null];
        }

        $middleIndex = null;
        $middle = null;

        foreach ($parts as $i => $part) {
            if ($i === 0) {
                continue;
            }

            // Single-letter middle initial: "M." or "M"
            if (preg_match('/^([A-Za-z])\.?$/u', $part, $m)) {
                $middleIndex = $i;
                $middle = $m[1];
                break;
            }
        }

        if ($middleIndex !== null) {
            $before = array_slice($parts, 0, $middleIndex);
            $after = array_slice($parts, $middleIndex + 1);

            $firstname = implode(' ', $before);
            $lastname = implode(' ', $after);

            if ($lastname === '') {
                // e.g. "Baguhin Lewil A." — treat last given name as last name
                if (count($before) > 1) {
                    $lastname = (string) array_pop($before);
                    $firstname = implode(' ', $before);
                } else {
                    $lastname = $firstname;
                }
            }

            return [$firstname, $lastname, $middle];
        }

        $lastname = (string) array_pop($parts);
        $firstname = implode(' ', $parts);

        return [$firstname, $lastname, null];
    }

    protected function resolveCourse(?string $program): ?string
    {
        if ($program === null || trim($program) === '') {
            return null;
        }

        $raw = trim($program);
        $codeKey = mb_strtoupper($raw);

        if ($this->programsByCode->has($codeKey)) {
            return $this->programsByCode->get($codeKey);
        }

        $nameKey = mb_strtolower($raw);
        if ($this->programsByName->has($nameKey)) {
            return $this->programsByName->get($nameKey);
        }

        // Partial match against registered program names (longest first)
        $bestCode = null;
        $bestLen = 0;
        foreach ($this->programsByName as $pname => $pcode) {
            if ($pname === '') {
                continue;
            }

            if (str_contains($nameKey, $pname) || str_contains($pname, $nameKey)) {
                $len = mb_strlen($pname);
                if ($len > $bestLen) {
                    $bestLen = $len;
                    $bestCode = $pcode;
                }
            }
        }

        if ($bestCode) {
            return $bestCode;
        }

        // Keyword fallbacks for common HSC programs not yet in the prospectus table
        $heuristics = [
            'early childhood' => 'BECED',
            'elementary education' => 'BEED',
            'secondary education' => 'BSED',
            'business administration' => 'BSBA',
            'financial management' => 'BSBA',
            'tourism management' => 'BSTM',
            'tourism' => 'BSTM',
            'computer science' => 'BSCS',
            'information technology' => 'BSIT',
            'nursing' => 'BSN',
        ];

        foreach ($heuristics as $needle => $code) {
            if (str_contains($nameKey, $needle)) {
                return $this->programsByCode->get(mb_strtoupper($code), $code);
            }
        }

        return $raw;
    }

    protected function normalizeIdNumber(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            // Avoid scientific notation for purely numeric IDs
            if (is_float($value) && floor($value) == $value) {
                return (string) (int) $value;
            }

            return trim((string) $value);
        }

        $id = trim((string) $value);

        return $id === '' ? null : $id;
    }

    protected function normalizeMobile(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            $digits = preg_replace('/\D+/', '', (string) (int) $value) ?? '';
        } else {
            $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
        }

        if ($digits === '') {
            return null;
        }

        // Excel often stores 10-digit PH mobiles without leading 0 (e.g. 9516556478)
        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            $digits = '0'.$digits;
        }

        // Keep a readable phone string; prefer 0-prefixed local form over raw digits only when short
        return $digits;
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }

    /**
     * @param  array<string|int, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeKeys(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            if (is_int($key)) {
                $normalized[$key] = $value;

                continue;
            }

            $slug = strtolower(trim((string) $key));
            $slug = preg_replace('/[^a-z0-9]+/', '_', $slug) ?? $slug;
            $slug = trim($slug, '_');
            $normalized[$slug] = $value;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    protected function hasAnyKey(array $row, array $keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return true;
            }
        }

        return false;
    }

    protected function generateNextQrCode(): string
    {
        $lastStudent = Student::query()
            ->whereNotNull('qrcode')
            ->where('qrcode', 'like', 'S-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastStudent && preg_match('/S-(\d+)/', (string) $lastStudent->qrcode, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        // Avoid collisions if gap exists from parallel imports within one run
        do {
            $code = 'S-'.str_pad((string) $nextNumber, 8, '0', STR_PAD_LEFT);
            $exists = Student::query()->where('qrcode', $code)->exists();
            $nextNumber++;
        } while ($exists);

        return $code;
    }
}

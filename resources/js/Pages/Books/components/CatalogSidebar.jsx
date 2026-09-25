import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';

function catalogParams(filters, overrides = {}) {
    const merged = { ...filters, ...overrides };
    const params = {};

    if (merged.show_all) {
        params.show_all = 1;
    }
    if (merged.search?.trim()) {
        params.search = merged.search.trim();
    }
    if (merged.program) {
        params.program = merged.program;
    }
    if (merged.year_filter) {
        params.year_filter = merged.year_filter;
    }
    if (merged.year1 !== '' && merged.year1 != null) {
        params.year1 = merged.year1;
    }
    if (merged.year2 !== '' && merged.year2 != null) {
        params.year2 = merged.year2;
    }
    if (merged.status) {
        params.status = merged.status;
    }
    if (merged.per_page) {
        params.per_page = merged.per_page;
    }

    return params;
}

function visitCatalog(filters, overrides = {}) {
    router.get('/book', catalogParams(filters, overrides), {
        preserveScroll: true,
        preserveState: true,
    });
}

function exportHref(filters) {
    const params = new URLSearchParams(catalogParams(filters));

    return `/export-books?${params.toString()}`;
}

function SectionLabel({ children }) {
    return (
        <p className="catalog-filter-label text-[10px] font-semibold uppercase tracking-[0.14em]">
            {children}
        </p>
    );
}

/** Solid brand purple — matches Search / Apply filters (never pale lavender). */
const solidPurpleBtn =
    'w-full !bg-[#af14e9] hover:!bg-[#8a0fb8] focus-visible:!bg-[#af14e9] active:!bg-[#8a0fb8] !text-white !opacity-100';

export function CatalogFilterSidebar({ programs, filters, hasActiveQuery }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [program, setProgram] = useState(filters.program ? String(filters.program) : '');
    const [yearFilter, setYearFilter] = useState(filters.year_filter || '');
    const [year1, setYear1] = useState(filters.year1 ?? '');
    const [year2, setYear2] = useState(filters.year2 ?? '');

    const localFilters = { ...filters, search, program, year_filter: yearFilter, year1, year2 };

    function submitSearch(event) {
        event.preventDefault();
        visitCatalog(localFilters, { show_all: false, page: undefined });
    }

    return (
        <Card className="sticky top-4 shadow-sm ring-border/60">
            <CardHeader className="border-b pb-3">
                <CardTitle className="text-sm font-semibold">Find books</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4 pt-4">
                <Button
                    type="button"
                    className={solidPurpleBtn}
                    variant="default"
                    onClick={() => visitCatalog(filters, { show_all: 1, page: undefined })}
                >
                    Show all books
                </Button>

                <form onSubmit={submitSearch} className="space-y-3">
                    {filters.status ? (
                        <input type="hidden" name="status" value={filters.status} />
                    ) : null}

                    <div className="space-y-1.5">
                        <Label htmlFor="catalog-search">Search</Label>
                        <Input
                            id="catalog-search"
                            placeholder="Title, author, accession…"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                        />
                    </div>

                    <div className="space-y-1.5">
                        <Label htmlFor="catalog-program">Program</Label>
                        <Select
                            value={program || 'all'}
                            onValueChange={(value) =>
                                setProgram(value === 'all' ? '' : value)
                            }
                        >
                            <SelectTrigger id="catalog-program" className="w-full">
                                <SelectValue placeholder="All programs" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All programs</SelectItem>
                                {programs.map((item) => (
                                    <SelectItem key={item.id} value={String(item.id)}>
                                        {item.program_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-1.5">
                        <Label htmlFor="catalog-year-filter">Publication year</Label>
                        <Select
                            value={yearFilter || 'none'}
                            onValueChange={(value) =>
                                setYearFilter(value === 'none' ? '' : value)
                            }
                        >
                            <SelectTrigger id="catalog-year-filter" className="w-full">
                                <SelectValue placeholder="Year filter" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Year filter</SelectItem>
                                <SelectItem value="exact">Exact</SelectItem>
                                <SelectItem value="before">Before</SelectItem>
                                <SelectItem value="after">After</SelectItem>
                                <SelectItem value="between">Between</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <Input
                        type="number"
                        placeholder="Year"
                        value={year1}
                        onChange={(event) => setYear1(event.target.value)}
                    />

                    {yearFilter === 'between' ? (
                        <Input
                            type="number"
                            placeholder="Year (end)"
                            value={year2}
                            onChange={(event) => setYear2(event.target.value)}
                        />
                    ) : null}

                    <Button type="submit" className={solidPurpleBtn} variant="default">
                        Search / Apply filters
                    </Button>

                    {hasActiveQuery ? (
                        <Button
                            type="button"
                            variant="outline"
                            className="w-full"
                            onClick={() => router.get('/book')}
                        >
                            Clear & start over
                        </Button>
                    ) : null}
                </form>

                <Separator />

                <div className="space-y-2">
                    <SectionLabel>Availability</SectionLabel>
                    <div className="grid gap-2">
                        <Button
                            type="button"
                            variant="available"
                            className={
                                filters.status === 'Available'
                                    ? 'w-full justify-center ring-2 ring-offset-2 ring-status-available'
                                    : 'w-full justify-center opacity-90'
                            }
                            onClick={() =>
                                visitCatalog(filters, {
                                    status: 'Available',
                                    show_all: true,
                                    page: undefined,
                                })
                            }
                        >
                            Available
                        </Button>
                        <Button
                            type="button"
                            variant="borrowed"
                            className={
                                filters.status === 'Borrowed'
                                    ? 'w-full justify-center ring-2 ring-offset-2 ring-status-borrowed'
                                    : 'w-full justify-center opacity-90'
                            }
                            onClick={() =>
                                visitCatalog(filters, {
                                    status: 'Borrowed',
                                    show_all: true,
                                    page: undefined,
                                })
                            }
                        >
                            Borrowed
                        </Button>
                    </div>
                </div>

                <Separator />

                <div className="space-y-2">
                    <SectionLabel>Catalog & collections</SectionLabel>
                    <div className="grid gap-2">
                        <Button asChild variant="default" className={solidPurpleBtn}>
                            <a href="/book/create">Cataloging</a>
                        </Button>
                        <Button asChild variant="default" className={solidPurpleBtn}>
                            <a href="/ebooks">View E-Resources</a>
                        </Button>
                        <Button asChild variant="default" className={solidPurpleBtn}>
                            <a href="/staff/books/archived">Archived</a>
                        </Button>
                        <Button asChild variant="destructive" className="w-full">
                            <a href="/staff/books/trash">Trash</a>
                        </Button>
                    </div>
                </div>

                <Separator />

                <div className="space-y-2">
                    <SectionLabel>Import / export</SectionLabel>
                    <form
                        action="/import-books"
                        method="POST"
                        encType="multipart/form-data"
                        className="space-y-2"
                    >
                        <input
                            type="hidden"
                            name="_token"
                            value={
                                document.querySelector('meta[name="csrf-token"]')?.content ??
                                ''
                            }
                        />
                        <Input
                            type="file"
                            name="file"
                            accept=".csv,.xlsx"
                            required
                            className="catalog-file-input text-xs file:mr-2 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary-foreground hover:file:bg-[color-mix(in_srgb,var(--primary)_82%,#000000)]"
                        />
                        <Button type="submit" variant="success" className="w-full">
                            Import books
                        </Button>
                    </form>
                    {hasActiveQuery ? (
                        <Button asChild variant="destructive" className="w-full">
                            <a href={exportHref(filters)}>Export results</a>
                        </Button>
                    ) : (
                        <Button type="button" variant="destructive" className="w-full" disabled>
                            Export books
                        </Button>
                    )}
                    {!hasActiveQuery ? (
                        <p className="text-center text-[11px] text-muted-foreground">
                            Search or filter first to export
                        </p>
                    ) : null}
                </div>
            </CardContent>
        </Card>
    );
}

export function CatalogEmptyState() {
    return (
        <Card className="border-dashed bg-muted/20 py-12 shadow-none">
            <CardContent className="flex flex-col items-center px-6 text-center">
                <div className="mb-4 flex size-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1.5"
                        className="size-7"
                        aria-hidden
                    >
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                    </svg>
                </div>
                <h2 className="text-lg font-semibold">Search or filter to view the catalog</h2>
                <p className="mt-2 max-w-md text-sm text-muted-foreground">
                    Use the panel on the left to search by title or author, filter by program or
                    publication year, or choose Available / Borrowed to load results here.
                </p>
                <Button
                    className="mt-6 !bg-[#af14e9] hover:!bg-[#8a0fb8] !text-white !opacity-100"
                    size="lg"
                    onClick={() => router.get('/book', { show_all: 1 })}
                >
                    Show all books
                </Button>
            </CardContent>
        </Card>
    );
}

export function CatalogResultsSummary({ books, filters }) {
    const showAllOnly =
        filters.show_all &&
        !filters.search &&
        !filters.program &&
        !filters.year1 &&
        !filters.status;

    return (
        <p className="text-sm text-muted-foreground">
            Showing {books.total} {books.total === 1 ? 'title' : 'titles'}
            {showAllOnly ? ' (entire catalog)' : null}
            {filters.search ? ` matching “${filters.search}”` : null}
            {filters.status ? ` · ${filters.status} only` : null}
        </p>
    );
}

export { catalogParams, visitCatalog };

function openDeleteModal(courseId, courseCode) {
    const form = document.getElementById('deleteForm');
    form.action = `/prospectus/course/${courseId}`;
    document.getElementById('deleteMessage').innerText =
        `Are you sure you want to delete course "${courseCode}"?`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function openEditModal(courseId, code, name) {
    const form = document.getElementById('editForm');
    form.action = `/prospectus/course/${courseId}`;
    document.getElementById('editCourseCode').value = code;
    document.getElementById('editCourseName').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('#prospectus-page [data-prospectus-panel]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const sel = btn.getAttribute('data-prospectus-panel');
            const panel = sel ? document.querySelector(sel) : null;
            if (panel) panel.classList.toggle('hidden');
        });
    });

    const editForm = document.getElementById('editForm');
    const deleteForm = document.getElementById('deleteForm');

    // Helper: show spinner
    function toggleLoading(button, loading) {
        const spinner = button.querySelector('.spinner');
        const text = button.querySelector('.btn-text');
        if (loading) {
            spinner.classList.remove('hidden');
            text.classList.add('hidden');
            button.disabled = true;
        } else {
            spinner.classList.add('hidden');
            text.classList.remove('hidden');
            button.disabled = false;
        }
    }

    // Helper: show success modal
    function showToast(message, type = "success") {
        const container = document.getElementById("toastContainer");

        const toast = document.createElement("div");
        toast.className = `px-4 py-2 rounded-lg shadow-lg text-white flex items-center justify-between w-64 animate-slide-in`;
        toast.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626"; // green or red
        toast.innerHTML = `
            <span>${message}</span>
            <button class="ml-2 text-white font-bold focus:outline-none">×</button>
        `;

        // remove on click
        toast.querySelector("button").addEventListener("click", () => toast.remove());

        // auto remove
        setTimeout(() => {
            toast.classList.remove("animate-slide-in");
            toast.classList.add("animate-fade-out");
            setTimeout(() => toast.remove(), 500);
        }, 2000);

        container.appendChild(toast);
    }

    // 🔹 Animations
    const style = document.createElement("style");
    style.innerHTML = `
        @keyframes slideIn { from { transform: translateX(100%); opacity:0; } to { transform: translateX(0); opacity:1; } }
        @keyframes fadeOut { from { opacity:1; } to { opacity:0; } }

        .animate-slide-in { animation: slideIn 0.4s ease-out; }
        .animate-fade-out { animation: fadeOut 0.5s forwards; }
    `;
    document.head.appendChild(style);


    // ✅ Handle Edit (AJAX)
    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('editBtn');
            toggleLoading(btn, true);

            let response = await fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            toggleLoading(btn, false);

            if (response.ok) {
                let updatedItem = await response.text();
                let courseId = this.action.split('/').pop();
                let li = document.getElementById('course-' + courseId);
                li.outerHTML = updatedItem;
                closeEditModal();
                showToast("Course Updated ✅");
            } else {
                alert('Error updating course');
            }
        });
    }

    // ✅ Handle Delete (AJAX)
    if (deleteForm) {
        deleteForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('deleteBtn');
            toggleLoading(btn, true);

            let response = await fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            toggleLoading(btn, false);

            if (response.ok) {
                let courseId = this.action.split('/').pop();
                let li = document.getElementById('course-' + courseId);
                if (li) li.remove();
                closeDeleteModal();
                showToast("Course Deleted 🗑️");
            } else {
                alert('Error deleting course');
            }
        });
    }

    document.querySelectorAll('.add-course-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            toggleLoading(btn, true);

            let formData = new FormData(this);
            let yearId = this.dataset.year;

            let response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            toggleLoading(btn, false);

            if (response.ok) {
                let newItem = await response.text();
                let ul = this.closest('#year-' + yearId).querySelector('ul');
                let emptyMsg = ul.querySelector('.text-gray-500');
                if (emptyMsg) emptyMsg.remove();
                ul.insertAdjacentHTML('beforeend', newItem);
                this.reset();
                showToast("Course Added ✅");
            } else {
                alert('Error adding course');
            }
        });
    });


    // 🔹 Open Program Edit Modal
    window.openProgramEditModal = function (programId, programCode, programName) {
        const modal = document.getElementById("editProgramModal");
        const form = document.getElementById("editProgramForm");
        form.action = `/prospectus/program/${programId}`;
        document.getElementById("editProgramCode").value = programCode;
        document.getElementById("editProgramName").value = programName;
        modal.classList.remove("hidden");
    };

    // 🔹 Close
    window.closeProgramEditModal = function () {
        document.getElementById("editProgramModal").classList.add("hidden");
    };

    // 🔹 Submit Handler
    const editProgramForm = document.getElementById("editProgramForm");
    if (editProgramForm) {
        editProgramForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            const btn = document.getElementById("editProgramBtn");
            toggleLoading(btn, true);

            let response = await fetch(this.action, {
                method: 'POST', // Laravel spoofing still works
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            toggleLoading(btn, false);

            if (response.ok) {
                let data = await response.json();
                // Update the display text
                document.getElementById("program-name-" + data.id).textContent =
                    `${data.program_code} — ${data.program_name}`;

                closeProgramEditModal();
                showToast("Program Updated 🎓");
            } else {
                showToast("Error updating program ❌", "error");
            }
        });
    }
    // 🔹 Open Delete Modal
    window.openProgramDeleteModal = function(programId, programCode) {
        const modal = document.getElementById("deleteProgramModal");
        const form = document.getElementById("deleteProgramForm");
        form.action = `/prospectus/program/${programId}`;
        document.getElementById("deleteProgramCode").textContent = programCode;
        modal.classList.remove("hidden");
    };
    
    // 🔹 Close
    window.closeProgramDeleteModal = function() {
        document.getElementById("deleteProgramModal").classList.add("hidden");
    };
    
    // 🔹 Submit Handler
    const deleteProgramForm = document.getElementById("deleteProgramForm");
    if (deleteProgramForm) {
        deleteProgramForm.addEventListener("submit", async function(e) {
            e.preventDefault();
            const btn = document.getElementById("deleteProgramBtn");
            toggleLoading(btn, true);
    
            let response = await fetch(this.action, {
                method: 'POST', // Laravel method spoofing
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
    
            toggleLoading(btn, false);
    
            if (response.ok) {
                let data = await response.json();
                // Remove the program card from DOM
                const programDiv = document.getElementById("program-name-" + data.id)?.closest(".bg-white.rounded.shadow.mb-6");
                if (programDiv) programDiv.remove();
    
                closeProgramDeleteModal();
                showToast("Program deleted 🗑️");
            } else {
                showToast("Error deleting program ❌", "error");
            }
        });
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const comboboxes = document.querySelectorAll("[role='combobox']");

    comboboxes.forEach((btn, index) => {
        const wrapper = btn.closest(".relative"); // tìm thẻ cha có position: relative
        const select = wrapper?.querySelector("select[aria-hidden='true']");
        if (!select) return;

        btn.addEventListener("click", function (e) {
            e.stopPropagation();

            // Đóng tất cả dropdown khác trước
            document.querySelectorAll(".custom-dropdown").forEach(dd => dd.remove());
            document.querySelectorAll("[role='combobox']").forEach(b => b.setAttribute("data-state", "closed"));

            const isOpen = btn.getAttribute("data-state") === "open";
            btn.setAttribute("data-state", isOpen ? "closed" : "open");

            if (!isOpen) {
                const dropdown = document.createElement("div");
                dropdown.className =
                    "custom-dropdown absolute mt-1 w-full rounded-md bg-white shadow border border-gray-200 z-50";

                [...select.options].forEach(opt => {
                    const item = document.createElement("div");
                    item.textContent = opt.text;
                    item.className = "px-3 py-2 hover:bg-gray-100 cursor-pointer";
                    item.addEventListener("click", () => {
                        btn.querySelector("span").textContent = opt.text;
                        select.value = opt.value;
                        dropdown.remove();
                        btn.setAttribute("data-state", "closed");
                    });
                    dropdown.appendChild(item);
                });

                wrapper.appendChild(dropdown);
            }
        });
    });

    // Đóng dropdown khi click ra ngoài
    document.addEventListener("click", function () {
        document.querySelectorAll(".custom-dropdown").forEach(dd => dd.remove());
        document.querySelectorAll("[role='combobox']").forEach(b => b.setAttribute("data-state", "closed"));
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('.scroll-link');

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            // Kiểm tra xem href có chứa dấu # ở đầu không
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetID = href.substring(1); // bỏ dấu #
                const target = document.getElementById(targetID);

                if (target) {
                    window.scrollTo({
                        top: target.offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
            // Nếu href không phải hash, để mặc định chuyển hướng
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('back-to-top');

    // Hiển thị nút khi scroll > 200px
    window.addEventListener('scroll', function () {
        if (window.scrollY > 200) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    });

    // Click scroll lên đầu trang mượt
    btn.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

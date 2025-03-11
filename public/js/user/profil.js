document.addEventListener("DOMContentLoaded", function () {
    /* Tabs for main content */

    // tabs button link
    const tabs = document.querySelectorAll(".tab-link");
    // tabs content
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            const target = this.getAttribute("data-tab");

            // Supprime la classe active de tous les onglets et cache le contenu
            tabs.forEach(t => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"));
            contents.forEach(c => c.classList.add("hidden"));

            // Active le bon onglet et affiche le bon contenu
            this.classList.add("text-white", "border-blue-500", "bg-gray-900/75");
            document.getElementById(target).classList.remove("hidden");
        });
    });

    /* Tabs for challenges subcontent */
    
    // subtabs button link
    const subTabs = document.querySelectorAll(".sub-tab-link");
    // subtabs content
    const subContents = document.querySelectorAll(".sub-tab-content");

    subTabs.forEach(subTab => {
        subTab.addEventListener("click", function () {
            const target = this.getAttribute("data-sub-tab");

            // Supprime la classe active de tous les onglets et cache le contenu
            subTabs.forEach(t => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"));
            subContents.forEach(c => c.classList.add("hidden"));

            // Active le bon onglet et affiche le bon contenu
            this.classList.add("text-white", "border-blue-500", "bg-gray-900/75");
            document.getElementById(target).classList.remove("hidden");
        });
    });
});
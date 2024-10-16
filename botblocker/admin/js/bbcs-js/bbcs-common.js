(function ($) {
    "use strict";

    function setLanguage(lang) {
        document.cookie = "preferred_language=" + lang + "; path=/";
        location.reload();
    }

    function getLanguageFromCookie() {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; preferred_language=`);
        if (parts.length === 2) return parts.pop().split(";").shift();
    }

    function loadTranslation(lang) {
        console.log("Loading translations for language:", lang);
        // Add code here to load translations
    }

    function initializeLanguageOptions() {
        const languageOptions = document.querySelectorAll(".language-option");
        const currentLang = getLanguageFromCookie();

        languageOptions.forEach((option) => {
            option.addEventListener("click", function (event) {
                event.preventDefault();
                const lang = this.getAttribute("data-lang");
                if (lang !== currentLang) {
                    setLanguage(lang);
                }
            });
        });

        if (currentLang) {
            loadTranslation(currentLang);
        }
    }

    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(
            document.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    $(document).ready(function () {
        initializeLanguageOptions();
        initializeTooltips();
    });

    function showConfirmClearModalReinstallDB(onConfirm) {
        var modal = $(
            '<div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">'
        );
        var modalDialog = $('<div class="modal-dialog">');
        var modalContent = $('<div class="modal-content">');
        var modalHeader = $(
            '<div class="modal-header"><h5 class="modal-title" id="confirmClearModalLabel">Re-install Database</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>'
        );
        var modalBody = $(
            '<div class="modal-body">Are you sure you want to re-install Database?</div>'
        );
        var modalFooter = $(
            '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button><button type="button" class="btn btn-primary" id="confirmClearButton">Yes</button></div>'
        );

        modalContent.append(modalHeader, modalBody, modalFooter);
        modalDialog.append(modalContent);
        modal.append(modalDialog);
        $("body").append(modal);

        $("#confirmClearButton").on("click", function () {
            $("#confirmClearModal").modal("hide");
            onConfirm();
        });

        $("#confirmClearModal").modal("show");
    }

    $(document).ready(function () {
        // Reinstalling the database and configuration files
        $("#bbcs-reinstall-database").on("click", function () {
            showConfirmClearModalReinstallDB(function () {
                $.ajax({
                    url: botblockerData.ajaxurl,
                    type: "POST",
                    data: {
                        action: "database_reinstallation",
                        nonce: botblockerData.nonce,
                    },
                    success: function (response) {
                        if (response.success) {
                            alert("Database reinstalled successfully!");
                            location.reload();
                        } else {
                            alert(
                                "Failed to reinstall database: " + response.data
                            );
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("AJAX Error: " + error);
                    },
                });
            });
        });
    });

    function showConfirmClearModalBackup(onConfirm) {
        var modal = $(
            '<div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">'
        );
        var modalDialog = $('<div class="modal-dialog">');
        var modalContent = $('<div class="modal-content">');
        var modalHeader = $(
            '<div class="modal-header"><h5 class="modal-title" id="confirmClearModalLabel">Backup</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>'
        );
        var modalBody = $(
            '<div class="modal-body">Are you sure you want to make a backup?</div>'
        );
        var modalFooter = $(
            '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button><button type="button" class="btn btn-primary" id="confirmClearButton">Yes</button></div>'
        );

        modalContent.append(modalHeader, modalBody, modalFooter);
        modalDialog.append(modalContent);
        modal.append(modalDialog);
        $("body").append(modal);

        $("#confirmClearButton").on("click", function () {
            $("#confirmClearModal").modal("hide");
            onConfirm();
        });

        $("#confirmClearModal").modal("show");
    }

    $(document).ready(function () {
        // dump all tables except hits
        $("#bbcs-backup-data-settings").on("click", function () {
            showConfirmClearModalBackup(function () {
                $.ajax({
                    url: botblockerData.ajaxurl,
                    type: "POST",
                    data: {
                        action: "backup_data_settings",
                        nonce: botblockerData.nonce,
                    },
                    success: function (response) {
                        if (response.success) {
                            window.location.href = response.data.download_url;
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        } else {
                            alert("Failed backup: " + response.data.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("AJAX Error: " + error);
                    },
                });
            });
        });
    });

    $(document).ready(function () {
        // Импорт данных из ZIP-архива
        $("#bbcs-import-data-settings").on("click", function () {
            var fileInput = $("<input>", {
                type: "file",
                accept: ".zip",
            }).on("change", function () {
                var file = this.files[0];
                if (file) {
                    var formData = new FormData();
                    formData.append("action", "import_data_settings");
                    formData.append("nonce", botblockerData.nonce);
                    formData.append("zip_file", file);

                    $.ajax({
                        url: botblockerData.ajaxurl,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.success) {
                                alert(
                                    "Import data and settings was successful!"
                                );
                            } else {
                                alert(
                                    "Failed import: " + response.data.message
                                );
                            }
                        },
                        error: function (xhr, status, error) {
                            alert("AJAX Error: " + error);
                        },
                    });
                }
            });
            fileInput.click();
        });
    });
})(jQuery);

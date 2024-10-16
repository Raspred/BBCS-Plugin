(function ($) {
    "use strict";

    function loadIpv4RulesTable() {
        if (!$.fn.DataTable.isDataTable("#botblocker-ipv4-rules")) {
            var table = $("#botblocker-ipv4-rules").DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                fixedHeader: true,
                responsive: true,
                colReorder: true,
                ajax: {
                    url: botblockerData.ajaxurl,
                    type: "POST",
                    data: function (d) {
                        d.action = "get_botblocker_ipv4_rules";
                        d.nonce = botblockerData.nonce;
                    },
                },
                columns: [
                    { data: "id", visible: false },
                    { data: "priority", width: "50px" },
                    { data: "ip", width: "80px" },
                    { data: "rule", width: "80px" },
                    { data: "expires", width: "100px" },
                    { data: "comment", width: "100px" },
                    {
                        data: null,
                        width: "100px",
                        render: function (data, type, row) {
                            return (
                                '<button class="btn btn-sm btn-default bbcs-actions-b edit-ipv4-rule" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit" data-id="' +
                                row.id +
                                '"><i class="fa-regular fa-edit"></i></button> ' +
                                '<button class="btn btn-sm btn-default bbcs-actions-b delete-ipv4-rule" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" data-id="' +
                                row.id +
                                '"><i class="fa-regular fa-trash-can"></i></button>'
                            );
                        },
                    },
                ],
                columnDefs: [
                    {
                        targets: "_all",
                        className: "text-wrap",
                    },
                ],
                layout: {
                    topStart: {
                        buttons: [
                            "copy",
                            "csv",
                            "excel",
                            "pdf",
                            "print",
                            "colvis",
                            {
                                extend: "collection",
                                text: "Length Menu",
                                buttons: [
                                    {
                                        text: "10",
                                        action: function (e, dt, node, config) {
                                            dt.page.len(10).draw();
                                        },
                                    },
                                    {
                                        text: "25",
                                        action: function (e, dt, node, config) {
                                            dt.page.len(25).draw();
                                        },
                                    },
                                    {
                                        text: "50",
                                        action: function (e, dt, node, config) {
                                            dt.page.len(50).draw();
                                        },
                                    },
                                    {
                                        text: "100",
                                        action: function (e, dt, node, config) {
                                            dt.page.len(100).draw();
                                        },
                                    },
                                ],
                            },
                        ],
                    },
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).css(
                        "background-color",
                        data.rule === "allow"
                            ? "rgba(0, 255, 0, 0.1)"
                            : "rgba(255, 0, 0, 0.1)"
                    );
                },
            });
            table.draw();
            table.columns.adjust();
        }
    }

    function showImportResultModal(result) {
        var modal = $(
            '<div class="modal fade" id="importResultModal" tabindex="-1" aria-labelledby="importResultModalLabel" aria-hidden="true">'
        );
        var modalDialog = $('<div class="modal-dialog">');
        var modalContent = $('<div class="modal-content">');
        var modalHeader = $(
            '<div class="modal-header"><h5 class="modal-title" id="importResultModalLabel">Import Result</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>'
        );
        var modalBody = $(
            '<div class="modal-body">' +
                "<p>Imported: " +
                result.imported +
                "</p>" +
                "<p>Skipped: " +
                result.skipped +
                "</p>" +
                "</div>"
        );
        var modalFooter = $(
            '<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>'
        );

        modalContent.append(modalHeader, modalBody, modalFooter);
        modalDialog.append(modalContent);
        modal.append(modalDialog);
        $("body").append(modal);

        $("#importResultModal").modal("show");
    }

    function showConfirmClearModal(onConfirm) {
        var modal = $(
            '<div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">'
        );
        var modalDialog = $('<div class="modal-dialog">');
        var modalContent = $('<div class="modal-content">');
        var modalHeader = $(
            '<div class="modal-header"><h5 class="modal-title" id="confirmClearModalLabel">Clear All Rules</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>'
        );
        var modalBody = $(
            '<div class="modal-body">Are you sure you want to remove all rules?</div>'
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

    function readJSONFile(file, callback) {
        var reader = new FileReader();
        reader.onload = function (e) {
            try {
                var data = JSON.parse(e.target.result);
                callback(data);
            } catch (err) {
                alert("Invalid JSON file: " + err.message);
            }
        };
        reader.readAsText(file);
    }

    $(document).ready(function () {
        // Инициализация таблицы только когда вкладка становится видимой
        $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
            var target = $(e.target).attr("href");
            if (target === "#bbcs_IPv4_list") {
                loadIpv4RulesTable();
            }
        });

        // Обновление значения приоритета при изменении ползунка
        $("#priority").on("input", function () {
            $("#priorityValue").val(this.value);
        });

        // Редактирование IPv4 правила
        $("#botblocker-ipv4-rules").on("click", ".edit-ipv4-rule", function () {
            var id = $(this).data("id");
            $.ajax({
                url: botblockerData.ajaxurl,
                type: "POST",
                data: {
                    action: "get_ipv4_rule_details",
                    id: id,
                    nonce: botblockerData.nonce,
                },
                success: function (response) {
                    if (response.success) {
                        var data = response.data;

                        // Преобразование expires в формат YYYY-MM-DDTHH:MM
                        var expiresTimestamp = data.expires;
                        var expiresFormattedDate = new Date(
                            expiresTimestamp * 1000
                        )
                            .toISOString()
                            .slice(0, 16);

                        $("#editIPv4Form").find('[name="id"]').val(data.id);
                        $("#editIPv4Form")
                            .find('[name="priority"]')
                            .val(data.priority);
                        $("#editIPv4Form").find('[name="ip"]').val(data.search);
                        $("#editIPv4Form")
                            .find('[name="comment"]')
                            .val(data.comment);
                        $("#editIPv4Form").find('[name="rule"]').val(data.rule);
                        $("#editIPv4Form")
                            .find('[name="expires"]')
                            .val(expiresFormattedDate);
                        $("#editIPv4Modal").modal("show");
                    } else {
                        alert(
                            "Failed to load IPv4 rule details: " + response.data
                        );
                    }
                },
            });
        });

        // Обновление IPv4 правила
        $("#editIPv4Form").on("submit", function (e) {
            e.preventDefault();
            $.ajax({
                url: botblockerData.ajaxurl,
                type: "POST",
                data:
                    $(this).serialize() +
                    "&action=update_ipv4_rule&nonce=" +
                    botblockerData.nonce,
                success: function (response) {
                    if (response.success) {
                        $("#editIPv4Modal").modal("hide");
                        $("#botblocker-ipv4-rules").DataTable().ajax.reload();
                    } else {
                        alert("Failed to update IPv4 rule: " + response.data);
                    }
                },
            });
        });

        // Удаление IPv4 правила
        $("#botblocker-ipv4-rules").on(
            "click",
            ".delete-ipv4-rule",
            function () {
                var id = $(this).data("id");
                if (
                    confirm("Are you sure you want to delete this IPv4 rule?")
                ) {
                    $.ajax({
                        url: botblockerData.ajaxurl,
                        type: "POST",
                        data: {
                            action: "delete_ipv4_rule",
                            id: id,
                            nonce: botblockerData.nonce,
                        },
                        success: function (response) {
                            if (response.success) {
                                $("#botblocker-ipv4-rules")
                                    .DataTable()
                                    .ajax.reload();
                            }
                        },
                    });
                }
            }
        );

        // Добавление нового IPv4 правила
        $("#bbcs_ipv4_add").on("click", function () {
            $("#addIPv4Modal").modal("show");

            // Устанавливаем значение по умолчанию для поля "expires"
            const form = document.getElementById("addIPv4Form");
            const expiresInput = form.querySelector("#addExpires");
            const now = new Date();
            now.setDate(now.getDate() + 30);
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, "0");
            const day = String(now.getDate()).padStart(2, "0");
            const hours = String(now.getHours()).padStart(2, "0");
            const minutes = String(now.getMinutes()).padStart(2, "0");

            const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;

            expiresInput.value = formattedDate;
        });

        // Создание нового IPv4 правила
        $("#addIPv4Form").on("submit", function (e) {
            e.preventDefault();
            $.ajax({
                url: botblockerData.ajaxurl,
                type: "POST",
                data:
                    $(this).serialize() +
                    "&action=create_ipv4_rule&nonce=" +
                    botblockerData.nonce,
                success: function (response) {
                    if (response.success) {
                        $("#addIPv4Modal").modal("hide");
                        $("#botblocker-ipv4-rules").DataTable().ajax.reload();
                    } else {
                        alert("Failed to create IPv4 rule: " + response.data);
                    }
                },
            });
        });

        // Экспорт IPv4 правил
        $("#bbcs_ipv4_export").on("click", function (e) {
            e.preventDefault();
            $.ajax({
                url: botblockerData.ajaxurl,
                type: "POST",
                data: {
                    action: "export_ipv4_rules",
                    nonce: botblockerData.nonce,
                },
                success: function (response) {
                    if (response.success) {
                        var blob = new Blob(
                            [JSON.stringify(response.data, null, 2)],
                            { type: "application/json" }
                        );
                        var downloadLink = document.createElement("a");
                        downloadLink.href = window.URL.createObjectURL(blob);
                        downloadLink.download = "botblocker_ipv4_rules.json";
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    } else {
                        alert("Failed to export IPv4 rules: " + response.data);
                    }
                },
            });
        });

        // Импорт IPv4 правил
        $("#bbcs_ipv4_import").on("click", function () {
            var fileInput = $("<input>", {
                type: "file",
                accept: "application/json",
            }).on("change", function () {
                var file = this.files[0];
                if (file) {
                    readJSONFile(file, function (data) {
                        $.ajax({
                            url: botblockerData.ajaxurl,
                            type: "POST",
                            data: {
                                action: "import_ipv4_rules",
                                rules: JSON.stringify(data),
                                nonce: botblockerData.nonce,
                            },
                            success: function (response) {
                                if (response.success) {
                                    showImportResultModal(response.data);
                                    $("#botblocker-ipv4-rules")
                                        .DataTable()
                                        .ajax.reload();
                                } else {
                                    alert(
                                        "Failed to import IPv4 rules: " +
                                            response.data
                                    );
                                }
                            },
                        });
                    });
                }
            });
            fileInput.click();
        });

        // Удаление всех IPv4 правил
        $("#bbcs_ipv4_clear_all").on("click", function () {
            showConfirmClearModal(function () {
                $.ajax({
                    url: botblockerData.ajaxurl,
                    type: "POST",
                    data: {
                        action: "clear_all_ipv4_rules",
                        nonce: botblockerData.nonce,
                    },
                    success: function (response) {
                        if (response.success) {
                            $("#botblocker-ipv4-rules")
                                .DataTable()
                                .ajax.reload();
                        } else {
                            alert(
                                "Failed to clear IPv4 rules: " + response.data
                            );
                        }
                    },
                });
            });
        });

        $("#bbcs_ipv4_import_white").on("click", function () {
            importIPv4List("whitelist");
        });

        $("#bbcs_ipv4_import_black").on("click", function () {
            importIPv4List("blacklist");
        });

        function importIPv4List(listType) {
            var fileInput = $("<input>", {
                type: "file",
                accept: ".txt",
            }).on("change", function () {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var fileContent = e.target.result;
                        $.ajax({
                            url: botblockerData.ajaxurl,
                            type: "POST",
                            data: {
                                action: "import_ipv4_" + listType,
                                file_content: fileContent,
                                nonce: botblockerData.nonce,
                            },
                            success: function (response) {
                                if (response.success) {
                                    showImportResultModal(response.data);
                                    $("#botblocker-ipv4-rules")
                                        .DataTable()
                                        .ajax.reload();
                                } else {
                                    alert(
                                        "Failed to import IPv4 " +
                                            listType +
                                            ": " +
                                            response.data
                                    );
                                }
                            },
                        });
                    };
                    reader.readAsText(file);
                }
            });
            fileInput.click();
        }
    });
})(jQuery);

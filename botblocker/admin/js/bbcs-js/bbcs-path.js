(function ($) {
    "use strict";

    var isProcessingPath = false;
  
    function initializePathsTable() {
      if (!$.fn.DataTable.isDataTable("#botblocker-paths")) {
        var table = $("#botblocker-paths").DataTable({
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
              d.action = "get_botblocker_paths";
              d.nonce = botblockerData.nonce;
            },
          },
          
          columns: [
            { data: "id", visible: false },
            { data: "priority", width: "80px" },
            { data: "search", width: "100px" },
            { data: "rule", width: "80px"},
            { data: "comment", width: "100px"},
            {
              data: null,
              width: "100px",
              render: function (data, type, row) {
                return (
                  '<button class="btn btn-sm btn-default bbcs-actions-b edit-path" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit" data-id="' +
                  row.id +
                  '"><i class="fa-regular fa-edit"></i></button> ' +
                  '<button class="btn btn-sm btn-default bbcs-actions-b delete-path"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete" data-id="' +
                  row.id +
                  '"><i class="fa-regular fa-trash-can"></i></button> ' +
                  '<button class="btn btn-sm bbcs-actions-b ' +
                  (row.disable == 0 ? "btn-default" : "btn-warning") +
                  ' toggle-path"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle On/Off" data-id="' +
                  row.id +
                  '"><i class="fas ' +
                  (row.disable == 0 ? "fa-stop" : "fa-play") +
                  '"></i></button>'
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
          createdRow: function (row, data, dataIndex) {
            $(row).css(
              "background-color",
              data.disable == 0 ? "rgba(0, 255, 0, 0.1)" : "rgba(255, 0, 0, 0.1)"
            );
          },
          layout: {
            topStart: {
              buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print', 'colvis',
                {
                  extend: 'collection',
                  text: 'Length Menu',
                  buttons: [
                    { text: '10', action: function ( e, dt, node, config ) { dt.page.len(10).draw(); } },
                    { text: '25', action: function ( e, dt, node, config ) { dt.page.len(25).draw(); } },
                    { text: '50', action: function ( e, dt, node, config ) { dt.page.len(50).draw(); } },
                    { text: '100', action: function ( e, dt, node, config ) { dt.page.len(100).draw(); } }
                  ]
                }
              ]
            }
          },
          drawCallback: function (settings) {
            var api = this.api();
            api.columns().every(function () {
              var column = this;
              var header = $(column.header());
              var body = $(column.nodes());
  
              if (body.length > 0) {
                header.css("min-width", body.first().css("width"));
                header.css("max-width", body.first().css("width"));
              }
            });
  
            api.columns.adjust();
          },
        });

        // Toggle path
        $(document).on("click", "#botblocker-paths .toggle-path", function (e) {
          e.preventDefault();
          if (isProcessingPath) return;
  
          var $button = $(this);
          var id = $button.data("id");
  
          isProcessingPath = true;
          $button.prop("disabled", true);
  
          $.ajax({
            url: botblockerData.ajaxurl,
            type: "POST",
            data: {
              action: "toggle_path",
              id: id,
              nonce: botblockerData.nonce,
            },
            success: function (response) {
              if (response.success) {
                var rowData = table.row($button.closest("tr")).data();
                rowData.disable = rowData.disable == 0 ? 1 : 0;
                table.row($button.closest("tr")).data(rowData).draw(false);
              }
            },
            complete: function () {
              isProcessingPath = false;
              $button.prop("disabled", false);
            },
          });
        });
      }
    }

    function showImportResultModal(result) {
        var modal = $('<div class="modal fade" id="importResultModal" tabindex="-1" aria-labelledby="importResultModalLabel" aria-hidden="true">');
        var modalDialog = $('<div class="modal-dialog">');
        var modalContent = $('<div class="modal-content">');
        var modalHeader = $('<div class="modal-header"><h5 class="modal-title" id="importResultModalLabel">Import Result</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>');
        var modalBody = $('<div class="modal-body">' + 
                          '<p>Imported: ' + result.imported + '</p>' +
                          '<p>Skipped: ' + result.skipped + '</p>' +
                          '</div>');
        var modalFooter = $('<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>');
    
        modalContent.append(modalHeader, modalBody, modalFooter);
        modalDialog.append(modalContent);
        modal.append(modalDialog);
        $("body").append(modal);
    
        $("#importResultModal").modal("show");
    }
    
    function showConfirmClearModal(onConfirm) {
        var modal = $('<div class="modal fade" id="confirmClearModal" tabindex="-1" aria-labelledby="confirmClearModalLabel" aria-hidden="true">');
        var modalDialog = $('<div class="modal-dialog">');
        var modalContent = $('<div class="modal-content">');
        var modalHeader = $('<div class="modal-header"><h5 class="modal-title" id="confirmClearModalLabel">Clear All Paths</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>');
        var modalBody = $('<div class="modal-body">Are you sure you want to remove all paths?</div>');
        var modalFooter = $('<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button><button type="button" class="btn btn-primary" id="confirmClearButton">Yes</button></div>');
    
        modalContent.append(modalHeader, modalBody, modalFooter);
        modalDialog.append(modalContent);
        modal.append(modalDialog);
        $("body").append(modal);
    
        $("#confirmClearButton").on("click", function() {
          $("#confirmClearModal").modal("hide");
          onConfirm();
        });
    
        $("#confirmClearModal").modal("show");
    }

    function readJSONFile(file, callback) {
        var reader = new FileReader();
        reader.onload = function(e) {
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
      $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#bbcs_path') {
          initializePathsTable();
        }
      });

        // Обновление значения приоритета при изменении ползунка
        $("#priority").on("input", function () {
            $("#priorityValue").val(this.value);
        });
          
        // Обработка отправки формы редактирования пути
        $("#editPathForm").on("submit", function (e) {
            e.preventDefault();
            $.ajax({
              url: botblockerData.ajaxurl,
              type: "POST",
              data:
                $(this).serialize() +
                "&action=update_path&nonce=" +
                botblockerData.nonce,
              success: function (response) {
                if (response.success) {
                  $("#editPathModal").modal("hide");
                  $("#botblocker-paths").DataTable().ajax.reload();
                } else {
                  alert("Failed to update path: " + response.data);
                }
              },
            });
        });
      
        // Загрузка данных пути при открытии модального окна редактирования
        $("#botblocker-paths").on("click", ".edit-path", function () {
            var id = $(this).data("id");
            $.ajax({
              url: botblockerData.ajaxurl,
              type: "POST",
              data: {
                action: "get_path_details",
                id: id,
                nonce: botblockerData.nonce,
              },
              success: function (response) {
                if (response.success) {
                  var data = response.data;
                  $("#editPathForm").find('[name="id"]').val(data.id);
                  $("#editPathForm").find('[name="priority"]').val(data.priority);
                  $("#priorityValue").val(data.priority);
                  $("#editPathForm").find('[name="search"]').val(data.search);
                  $("#editPathForm").find('[name="comment"]').val(data.comment);
                  $("#editPathForm").find('[name="rule"]').val(data.rule);
                  $("#editPathModal").modal("show");
                } else {
                  alert("Failed to load path details: " + response.data);
                }
              },
            });
        });
      
        // Удаление пути
        $("#botblocker-paths").on("click", ".delete-path", function () {
            var id = $(this).data("id");
            if (confirm("Are you sure you want to delete this path?")) {
              $.ajax({
                url: botblockerData.ajaxurl,
                type: "POST",
                data: {
                  action: "delete_path",
                  id: id,
                  nonce: botblockerData.nonce,
                },
                success: function (response) {
                  if (response.success) {
                    $("#botblocker-paths").DataTable().ajax.reload();
                  }
                },
              });
            }
        });
      
        // Добавление нового пути
        $("#bbcs_path_add").on("click", function() {
            $("#createPathModal").modal("show");
        });
      
        // Обработка отправки формы создания нового пути
        $("#createPathForm").on("submit", function(e) {
            e.preventDefault();
            $.ajax({
              url: botblockerData.ajaxurl,
              type: "POST",
              data: $(this).serialize() + "&action=create_path&nonce=" + botblockerData.nonce,
              success: function(response) {
                if (response.success) {
                  $("#createPathModal").modal("hide");
                  $("#botblocker-paths").DataTable().ajax.reload();
                } else {
                  alert("Failed to create path: " + response.data);
                }
              },
            });
        });
      
        // Экспорт путей в JSON
        $("#bbcs_path_export").on("click", function(e) {
            e.preventDefault();
            $.ajax({
              url: botblockerData.ajaxurl,
              type: "POST",
              data: {
                action: "export_paths",
                nonce: botblockerData.nonce,
              },
              success: function(response) {
                if (response.success) {
                  var blob = new Blob([JSON.stringify(response.data, null, 2)], { type: "application/json" });
                  var downloadLink = document.createElement("a");
                  downloadLink.href = window.URL.createObjectURL(blob);
                  downloadLink.download = "botblocker_paths.json";
                  document.body.appendChild(downloadLink);
                  downloadLink.click();
                  document.body.removeChild(downloadLink);
                } else {
                  alert("Failed to export paths: " + response.data);
                }
              },
            });
        });
      
        // Импорт путей из JSON
        $("#bbcs_path_import").on("click", function() {
            var fileInput = $("<input>", {
              type: "file",
              accept: "application/json",
            }).on("change", function() {
              var file = this.files[0];
              if (file) {
                readJSONFile(file, function(data) {
                  $.ajax({
                    url: botblockerData.ajaxurl,
                    type: "POST",
                    data: {
                      action: "import_paths",
                      paths: JSON.stringify(data),
                      nonce: botblockerData.nonce,
                    },
                    success: function(response) {
                      if (response.success) {
                        showImportResultModal(response.data);
                        $("#botblocker-paths").DataTable().ajax.reload();
                      } else {
                        alert("Failed to import paths: " + response.data);
                      }
                    },
                  });
                });
              }
            });
            fileInput.click();
        });
      
        // Удаление всех путей
        $("#bbcs_path_clear_all").on("click", function() {
            showConfirmClearModal(function() {
              $.ajax({
                url: botblockerData.ajaxurl,
                type: "POST",
                data: {
                  action: "clear_all_paths",
                  nonce: botblockerData.nonce,
                },
                success: function(response) {
                  if (response.success) {
                    $("#botblocker-paths").DataTable().ajax.reload();
                  } else {
                    alert("Failed to clear paths: " + response.data);
                  }
                },
              });
            });
        });          
    });      
})(jQuery);

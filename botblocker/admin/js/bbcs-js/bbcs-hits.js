(function ($) {
    "use strict";
  
    var tables = {
      "botblocker-hits": { initialized: false, action: "get_botblocker_hits" },
      "botblocker-hits-admin": {
        initialized: false,
        action: "get_botblocker_admin_hits",
      },
      "botblocker-other-admin": {
        initialized: false,
        action: "get_botblocker_other_hits",
      },
    };
  
    function initializeDataTable(tableId) {
      if (
        !$.fn.DataTable.isDataTable("#" + tableId) &&
        !tables[tableId].initialized
      ) {
        $("#" + tableId).DataTable({
          processing: true,
          serverSide: true,
          scrollX: true,
          fixedHeader: true,
          responsive: true,
          colReorder: true,
          autoWidth: false,
          ajax: {
            url: botblockerData.ajaxurl,
            type: "POST",
            data: function (d) {
              d.action = tables[tableId].action;
              d.nonce = botblockerData.nonce;
            },
          },
          columns: [
            { data: "date", width: "85px" },
            { data: "time", width: "60px" },
            { data: "ip", width: "80px" },
            { data: "ptr", width: "100px" },
            {
              data: "as_info",
              width: "100px",
              render: function (data) {
                return data.asnum + "<br>" + data.asname;
              },
            },
            { data: "country", width: "50px" },
            { data: "lang", width: "60px" },
            { data: "useragent", width: "200px" },
            { data: "referer", width: "150px" },
            { data: "page", width: "150px" },
            {
              data: "js_info",
              width: "150px",
              render: function (data) {
                return (
                  "Display Width: " +
                  data.js_w +
                  "<br>" +
                  "Display Height: " +
                  data.js_h +
                  "<br>" +
                  "Client Width: " +
                  data.js_cw +
                  "<br>" +
                  "Client Height: " +
                  data.js_ch +
                  "<br>" +
                  "Color Depth: " +
                  data.js_co +
                  "<br>" +
                  "Pixel Depth: " +
                  data.js_pi
                );
              },
            },
            { data: "adblock", width: "50px" },
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
                'copy', 'csv', 'excel', 'pdf', 'print', 'colvis',
                {
                  extend: 'collection',
                  text: 'Length Menu',
                  buttons: [
                    { text: '10', action: function ( e, dt, node, config ) { dt.page.len(10).draw(); } },
                    { text: '25', action: function ( e, dt, node, config ) { dt.page.len(25).draw(); } },
                    { text: '50', action: function ( e, dt, node, config ) { dt.page.len(50).draw(); } },
                    { text: 'All', action: function ( e, dt, node, config ) { dt.page.len(-1).draw(); } }
                  ]
                }
              ]
            }
          },
          initComplete: function (settings, json) {
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
  
            api.columns.adjust().draw();
          },
        });
        tables[tableId].initialized = true;
      }
    }
  

    $(document).ready(function () {
      initializeDataTable("botblocker-hits");

      $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#admin') {
          initializeDataTable("botblocker-hits-admin");
        }
      });
      $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#wordpress') {
          initializeDataTable("botblocker-other-admin");
        }
      });
    });
  
  })(jQuery);
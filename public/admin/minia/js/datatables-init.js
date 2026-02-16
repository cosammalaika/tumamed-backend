(function () {
    function createConfig() {
        return {
            responsive: true,
            processing: true,
            deferRender: true,
            searchDelay: 250,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            autoWidth: false,
            order: [],
            dom: "<'row align-items-center mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row align-items-center mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'copy', className: 'btn btn-sm btn-primary' },
                { extend: 'excel', className: 'btn btn-sm btn-primary' },
                { extend: 'pdf', className: 'btn btn-sm btn-primary' },
                { extend: 'colvis', className: 'btn btn-sm btn-primary' },
            ],
            columnDefs: [
                {
                    targets: 'no-sort',
                    orderable: false,
                    searchable: false,
                    width: '140px',
                    className: 'text-end table-actions',
                },
            ],
            language: {
                search: 'Search:',
                searchPlaceholder: 'Search...',
            },
        };
    }

    function initTables(root) {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

        var scope = root || document;
        jQuery(scope).find('table.js-datatable').each(function () {
            var table = this;

            if (jQuery.fn.DataTable.isDataTable(table)) {
                return;
            }

            jQuery(table).DataTable(createConfig());
        });
    }

    function reinitTables(root) {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

        var scope = root || document;
        jQuery(scope).find('table.js-datatable').each(function () {
            var table = this;

            if (jQuery.fn.DataTable.isDataTable(table)) {
                jQuery(table).DataTable().destroy();
            }
        });

        initTables(scope);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTables(document);
    });

    document.addEventListener('livewire:navigated', function () {
        reinitTables(document);
    });

    document.addEventListener('livewire:initialized', function () {
        if (!window.Livewire || typeof window.Livewire.hook !== 'function') return;

        window.Livewire.hook('message.processed', function () {
            reinitTables(document);
        });
    });
})();

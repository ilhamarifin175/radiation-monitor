// Call the dataTables jQuery plugin
$(document).ready(function() {
    $('#dataTable').DataTable({
        dom: "<'row mb-2'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12 table-responsive'tr>>" +
             "<'row mt-2 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-md-end'p>>",
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']],
        pagingType: 'full_numbers',
        buttons: [
            {
                extend: 'csvHtml5',
                text: '<i class="fas fa-file-csv mr-1"></i> Export CSV',
                className: 'btn btn-sm btn-secondary mr-1',
                title: 'Data Table | ' + (document.title.split(' | ')[1] || document.title).replace(' Data Tables', '')
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel mr-1"></i> Export Excel',
                className: 'btn btn-sm btn-success mr-1',
                title: 'Data Table | ' + (document.title.split(' | ')[1] || document.title).replace(' Data Tables', '')
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf mr-1"></i> Export PDF',
                className: 'btn btn-sm btn-danger',
                title: 'Data Table | ' + (document.title.split(' | ')[1] || document.title).replace(' Data Tables', ''),
                orientation: 'landscape',
                pageSize: 'A4'
            }
        ],
        initComplete: function () {
            $('#dataTable').show();
        },
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Tidak ada data yang cocok',
            paginate: {
                first: '&laquo;',
                last: '&raquo;',
                next: '&rsaquo;',
                previous: '&lsaquo;'
            }
        }
    });
});

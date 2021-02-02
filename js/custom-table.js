// $(document).ready(function() {
//     $('#usertable').DataTable();
// } );

$(document).ready(function() {
    $('#usertable').DataTable( {
        dom: 'Bfrtip',
        buttons: [ 
            'copy', 'csv', 'excel',
        ]
    } );
} );

$(document).ready(function() {
    $('#studenttable').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'student report'
            }
        ]
    } );
} );

$(document).ready(function() {
    $('#trainertable').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Trainer report'
            }
        ]
    } );
} );
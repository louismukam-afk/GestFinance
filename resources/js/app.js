import $ from 'jquery';
window.$ = $;
window.jQuery = $;

// 🔹 DataTables de base + Bootstrap 5
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';       // ✅ Responsive
import 'datatables.net-buttons-bs5';          // ✅ Boutons Bootstrap 5
import 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.print';
import 'datatables.net-responsive-bs5';
// 🔹 Librairies nécessaires pour export
import jszip from 'jszip';
/*import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';*/

import pdfMake from 'pdfmake/build/pdfmake';
import * as pdfFonts from 'pdfmake/build/vfs_fonts';

pdfMake.vfs = pdfFonts.default?.vfs || pdfFonts.vfs;
window.pdfMake = pdfMake;

//Ici ce n’est pas pdfFonts.pdfMake.vfs mais directement pdfFonts.vfs
// pdfMake.vfs = pdfFonts.vfs;
// window.pdfMake = pdfMake;

// Rendre dispo pour DataTables
window.JSZip = jszip;
window.pdfMake = pdfMake;
window.DataTable = DataTable;

import './bootstrap';

// ⚡ Initialisation DataTable
$(function () {
    const $table = $('#tableProduits');
    if ($table.length) {
        $table.DataTable({
            responsive: true,
            dom: 'Bfrtip',
            pageLength: 25,
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '📊 Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '📄 PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'print',
                    text: '🖨 Imprimer',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: ':not(:last-child)' }
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
            }
        });
    }
});

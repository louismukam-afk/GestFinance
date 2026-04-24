<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\bon_commandeok;
use App\Models\element_bon_commande;
use App\Models\personnel;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BonsExport;

class EtatBonCommandeController extends Controller
{
    public function index(Request $request)
    {
        $query = bon_commandeok::with(['personnels', 'entites']);

        // 📌 Filtre par période
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin]);
        }

        // 📌 Filtre par personnel
        if ($request->filled('id_personnel')) {
            $query->where('id_personnel', $request->id_personnel);
        }

        // 📌 Filtre par utilisateur
        if ($request->filled('id_user')) {
            $query->where('id_user', $request->id_user);
        }

        $bons = $query->orderBy('date_debut', 'desc')->get();
        $personnels = personnel::all();
        $users = User::all();

        return view('Admin.etat_bons.index', compact('bons', 'personnels', 'users'));
    }

    // 📌 Export Excel
    public function exportExcel(Request $request)
    {
        return Excel::download(new BonsExport($request->all()), 'etat_bons.xlsx');
    }

    // 📌 Export PDF (liste des bons)
    public function exportPdf(Request $request)
    {
        $query = bon_commandeok::with(['personnels', 'entites']);

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin]);
        }
        if ($request->filled('id_personnel')) {
            $query->where('id_personnel', $request->id_personnel);
        }
        if ($request->filled('id_user')) {
            $query->where('id_user', $request->id_user);
        }

        $bons = $query->get();
        $pdf = Pdf::loadView('Admin.etat_bons.pdf', compact('bons'));
        return $pdf->download('etat_bons.pdf');
    }

    // 📌 Détails d’un bon (avec ses éléments)
    public function show($id)
    {
        $bon = bon_commandeok::with(['personnels', 'entites'])->findOrFail($id);
        $elements = element_bon_commande::where('id_bon_commande', $id)->get();

        return view('Admin.etat_bons.show', compact('bon', 'elements'));
    }

    // 📌 Export PDF d’un bon avec ses éléments
    public function exportBonPdf($id)
    {
        $bon = bon_commandeok::with(['personnels', 'entites'])->findOrFail($id);
        $elements = element_bon_commande::where('id_bon_commande', $id)->get();

        $pdf = Pdf::loadView('Admin.etat_bons.bon_pdf', compact('bon', 'elements'));
        return $pdf->download("bon_commande_{$bon->id}.pdf");
    }


    /**
     * Export global PDF (sur période).
     */
    public function exportPdf1(Request $request)
    {
        $bons = bon_commandeok::with(['personnels', 'user'])
            ->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
            ->get();

        $pdf = PDF::loadView('Admin.etat_bons.pdf', compact('bons'));
        return $pdf->download('bons_de_commande.pdf');
    }

    /**
     * Export global Excel (sur période).
     */
    /* public function exportExcel(Request $request)
     {
         return Excel::download(new BonsExport($request->date_debut, $request->date_fin), 'bons_de_commande.xlsx');
     }*/

    /**
     * Export PDF d’un seul bon.
     */
    public function exportPdfOne($id)
    {
        $bon = bon_commandeok::with(['personnels', 'user'])->findOrFail($id);
        $elements = element_bon_commande::where('id_bon_commande', $id)->get();

        $pdf = PDF::loadView('Admin.etat_bons.pdf_one', compact('bon', 'elements'));
        return $pdf->download("bon_commande_{$bon->id}.pdf");
    }

    public function exportPdfOne1($id)
    {
        $bon = bon_commandeok::with('personnels','user','entites','elements')->findOrFail($id);
        $pdf = PDF::loadView('Admin.etat_bons.pdf_one', compact('bon'));
        return $pdf->download("bon_commande_{$bon->id}.pdf");
    }
    public function exportExcelOne1($id)
    {
        return Excel::download(new BonsOneExport($id), "bon_commande_{$id}.xlsx");
    }

    /**
     * Export Excel d’un seul bon.
     */
    public function exportExcelOne($id)
    {
        $bon = bon_commandeok::with(['personnels', 'user'])->findOrFail($id);
        $elements = element_bon_commande::where('id_bon_commande', $id)->get();

        return Excel::download(new BonsExport(null, null, $bon->id), "bon_commande_{$bon->id}.xlsx");
    }
}

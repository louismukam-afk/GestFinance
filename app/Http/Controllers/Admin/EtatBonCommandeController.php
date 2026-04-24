<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\bon_commandeok;
use App\Models\personnel;
use App\Models\User;
use Illuminate\Http\Request;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BonsExport1;
use App\Exports\BonsOneExport;

class EtatBonCommandeController extends Controller
{
    /**
     * Liste filtrée des bons de commande
     */
    public function index(Request $request)
    {
        $query = bon_commandeok::with(['personnels', 'user', 'entites', 'element_bon_commandes'])
            ->orderBy('date_debut', 'desc');

        // 🔹 Filtrer par période
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin]);
        }

        // 🔹 Filtrer par personnel
        if ($request->filled('id_personnel')) {
            $query->where('id_personnel', $request->id_personnel);
        }

        // 🔹 Filtrer par utilisateur
        if ($request->filled('id_user')) {
            $query->where('id_user', $request->id_user);
        }

        $bons = $query->get();
        $personnels = personnel::all();
        $users = User::all();

        return view('Admin.etat_bons.index', compact('bons', 'personnels', 'users'));
    }

    /**
     * Afficher un bon + ses éléments
     */
    public function show($id)
    {
        $bon = bon_commandeok::with('personnels','user','entites','element_bon_commandes')->findOrFail($id);
       /* dump($bon);
        die();*/
        return view('Admin.etat_bons.show', compact('bon'));
    }

    /**
     * Exporter PDF (filtré)
     */
    public function exportPdf(Request $request)
    {
        $bons = $this->filterBons($request)->get();
        $pdf = PDF::loadView('Admin.etat_bons.pdf', compact('bons'));
        return $pdf->download("etat_bons.pdf");
    }

    /**
     * Exporter Excel (filtré)
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new BonsExport1($request), 'etat_bons.xlsx');
    }

    /**
     * Exporter un seul bon en PDF
     */
    public function exportPdfOne($id)
    {
        $bon = bon_commandeok::with('personnels','user','entites','element_bon_commandes')->findOrFail($id);
        $pdf = PDF::loadView('Admin.etat_bons.pdf_one', compact('bon'));
        return $pdf->download("bon_commande_{$bon->id}.pdf");
    }

    /**
     * Exporter un seul bon en Excel
     */
    public function exportExcelOne($id)
    {
        return Excel::download(new BonsOneExport($id), "bon_commande_{$id}.xlsx");
    }

    /**
     * Méthode privée de filtre réutilisable
     */
    private function filterBons(Request $request)
    {
        $query = bon_commandeok::with(['personnels', 'user', 'entites', 'element_bon_commandes'])
            ->orderBy('date_debut', 'desc');

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin]);
        }

        if ($request->filled('id_personnel')) {
            $query->where('id_personnel', $request->id_personnel);
        }

        if ($request->filled('id_user')) {
            $query->where('id_user', $request->id_user);
        }

        return $query;
    }
}

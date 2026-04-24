<?php

namespace App\Admin\Controllers;

use App\Models\Etudiant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class EtudiantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Etudiant';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Etudiant());

        $grid->column('id', __('Id'));
        $grid->column('nom', __('Nom'));
        $grid->column('telephone_whatsapp', __('Telephone whatsapp'));
        $grid->column('date_naissance', __('Date naissance'));
        $grid->column('lieu_naissance', __('Lieu naissance'));
        $grid->column('sexe', __('Sexe'));
        $grid->column('email', __('Email'));
        $grid->column('adresse', __('Adresse'));
        $grid->column('departement_origine', __('Departement origine'));
        $grid->column('region_origine', __('Region origine'));
        $grid->column('nom_pere', __('Nom pere'));
        $grid->column('telephone_whatsapp_pere', __('Telephone whatsapp pere'));
        $grid->column('nom_mere', __('Nom mere'));
        $grid->column('nom_tuteur', __('Nom tuteur'));
        $grid->column('telephone_tuteur', __('Telephone tuteur'));
        $grid->column('matricule', __('Matricule'));
        $grid->column('telephone_2_etudiants', __('Telephone 2 etudiants'));
        $grid->column('adresse_tuteur', __('Adresse tuteur'));
        $grid->column('dernier_etablissement_frequente', __('Dernier etablissement frequente'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Etudiant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom', __('Nom'));
        $show->field('telephone_whatsapp', __('Telephone whatsapp'));
        $show->field('date_naissance', __('Date naissance'));
        $show->field('lieu_naissance', __('Lieu naissance'));
        $show->field('sexe', __('Sexe'));
        $show->field('email', __('Email'));
        $show->field('adresse', __('Adresse'));
        $show->field('departement_origine', __('Departement origine'));
        $show->field('region_origine', __('Region origine'));
        $show->field('nom_pere', __('Nom pere'));
        $show->field('telephone_whatsapp_pere', __('Telephone whatsapp pere'));
        $show->field('nom_mere', __('Nom mere'));
        $show->field('nom_tuteur', __('Nom tuteur'));
        $show->field('telephone_tuteur', __('Telephone tuteur'));
        $show->field('matricule', __('Matricule'));
        $show->field('telephone_2_etudiants', __('Telephone 2 etudiants'));
        $show->field('adresse_tuteur', __('Adresse tuteur'));
        $show->field('dernier_etablissement_frequente', __('Dernier etablissement frequente'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Etudiant());

        $form->text('nom', __('Nom'));
        $form->text('telephone_whatsapp', __('Telephone whatsapp'));
        $form->date('date_naissance', __('Date naissance'))->default(date('Y-m-d'));
        $form->text('lieu_naissance', __('Lieu naissance'));
        $form->text('sexe', __('Sexe'));
        $form->email('email', __('Email'));
        $form->text('adresse', __('Adresse'));
        $form->text('departement_origine', __('Departement origine'));
        $form->text('region_origine', __('Region origine'));
        $form->text('nom_pere', __('Nom pere'));
        $form->text('telephone_whatsapp_pere', __('Telephone whatsapp pere'));
        $form->text('nom_mere', __('Nom mere'));
        $form->text('nom_tuteur', __('Nom tuteur'));
        $form->text('telephone_tuteur', __('Telephone tuteur'));
        $form->text('matricule', __('Matricule'));
        $form->text('telephone_2_etudiants', __('Telephone 2 etudiants'));
        $form->text('adresse_tuteur', __('Adresse tuteur'));
        $form->text('dernier_etablissement_frequente', __('Dernier etablissement frequente'));

        return $form;
    }
}

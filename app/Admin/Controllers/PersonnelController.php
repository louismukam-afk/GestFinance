<?php

namespace App\Admin\Controllers;

use App\Models\personnel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PersonnelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'personnel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new personnel());

        $grid->column('id', __('Id'));
        $grid->column('nom', __('Nom'));
        $grid->column('date_naissance', __('Date naissance'));
        $grid->column('lieu_naissance', __('Lieu naissance'));
        $grid->column('adresse', __('Adresse'));
        $grid->column('sexe', __('Sexe'));
        $grid->column('statut_matrimonial', __('Statut matrimonial'));
        $grid->column('email', __('Email'));
        $grid->column('telephone', __('Telephone'));
        $grid->column('telephone_whatsapp', __('Telephone whatsapp'));
        $grid->column('diplome', __('Diplome'));
        $grid->column('niveau_etude', __('Niveau etude'));
        $grid->column('domaine_formation', __('Domaine formation'));
        $grid->column('date_recrutement', __('Date recrutement'));
        $grid->column('users.name', __('Utilisateur'));
        $grid->column('nationalite', __('Nationalite'));
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
        $show = new Show(personnel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom', __('Nom'));
        $show->field('date_naissance', __('Date naissance'));
        $show->field('lieu_naissance', __('Lieu naissance'));
        $show->field('adresse', __('Adresse'));
        $show->field('sexe', __('Sexe'));
        $show->field('statut_matrimonial', __('Statut matrimonial'));
        $show->field('email', __('Email'));
        $show->field('telephone', __('Telephone'));
        $show->field('telephone_whatsapp', __('Telephone whatsapp'));
        $show->field('diplome', __('Diplome'));
        $show->field('niveau_etude', __('Niveau etude'));
        $show->field('domaine_formation', __('Domaine formation'));
        $show->field('date_recrutement', __('Date recrutement'));
        $show->field('id_user', __('Id user'));
        $show->field('nationalite', __('Nationalite'));
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
        $form = new Form(new personnel());

        $form->text('nom', __('Nom'));
        $form->date('date_naissance', __('Date naissance'))->default(date('Y-m-d'));
        $form->text('lieu_naissance', __('Lieu naissance'));
        $form->text('adresse', __('Adresse'));
        $form->text('sexe', __('Sexe'));
        $form->text('statut_matrimonial', __('Statut matrimonial'));
        $form->email('email', __('Email'));
        $form->text('telephone', __('Telephone'));
        $form->text('telephone_whatsapp', __('Telephone whatsapp'));
        $form->text('diplome', __('Diplome'));
        $form->text('niveau_etude', __('Niveau etude'));
        $form->text('domaine_formation', __('Domaine formation'));
        $form->date('date_recrutement', __('Date recrutement'))->default(date('Y-m-d'));
        $form->hidden('id_user');
        $form->saving(function (Form $form) {
            $form->id_user = \Admin::user()->id; // ou Auth::id() si hors de Laravel Admin
        });
        $form->text('nationalite', __('Nationalite'));

        return $form;
    }
}

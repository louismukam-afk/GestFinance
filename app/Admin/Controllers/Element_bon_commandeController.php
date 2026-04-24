<?php

namespace App\Admin\Controllers;

use App\Models\bon_commandeok;
use App\Models\element_bon_commande;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class Element_bon_commandeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'element_bon_commande';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new element_bon_commande());

        $grid->column('id', __('Id'));
        $grid->column('nom_element_bon_commande', __('Nom element bon commande'));
        $grid->column('description_elements_bon_commande', __('Description elements bon commande'));
        $grid->column('quantite_element_bon_commande', __('Quantite element bon commande'));
        $grid->column('id_user', __('Id user'));
        $grid->column('id_bon_commande', __('Id bon commande'));
        $grid->column('prix_unitaire_element_bon_commande', __('Prix unitaire element bon commande'));
        $grid->column('montant_total_element_bon_commande', __('Montant total element bon commande'));
        $grid->column('date_realisation', __('Date realisation'));
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
        $show = new Show(element_bon_commande::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom_element_bon_commande', __('Nom element bon commande'));
        $show->field('description_elements_bon_commande', __('Description elements bon commande'));
        $show->field('quantite_element_bon_commande', __('Quantite element bon commande'));
        $show->field('id_user', __('Id user'));
        $show->field('id_bon_commande', __('Id bon commande'));
        $show->field('prix_unitaire_element_bon_commande', __('Prix unitaire element bon commande'));
        $show->field('montant_total_element_bon_commande', __('Montant total element bon commande'));
        $show->field('date_realisation', __('Date realisation'));
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
        $form = new Form(new element_bon_commande());

        $form->text('nom_element_bon_commande', __('Nom element bon commande'));
        $form->text('description_elements_bon_commande', __('Description elements bon commande'));
        $form->number('quantite_element_bon_commande', __('Quantite element bon commande'));
        //$form->number('id_user', __('Id user'));
        $form->saving(function (Form $form) {
            $form->id_user = Admin::user()->id; // ou Auth::id() si hors de Laravel Admin
        });
        $form->select('id_bon_commande', __('Bon de commande'))
            ->options(bon_commandeok::pluck('nom_bon_commande', 'id')->toArray())
            ->default(request()->get('id_bon_commande'))
            ->required();
            /*->disable(); // l’utilisateur ne pourra pas le modifier*/

        /* $form->select('id_bon_commande', __('bon_commandeok'))
             ->options(bon_commandeok::orderBy('nom_bon_commande')->pluck('nom_bon_commande', 'id'))
             ->required();*/
//        $form->number('id_bon_commande', __('Id bon commande'));
        $form->decimal('prix_unitaire_element_bon_commande', __('Prix unitaire element bon commande'));
        $form->decimal('montant_total_element_bon_commande', __('Montant total element bon commande'));
        $form->date('date_realisation', __('Date realisation'))->default(date('Y-m-d'));

        return $form;
    }
}

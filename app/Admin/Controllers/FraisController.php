<?php

namespace App\Admin\Controllers;

use App\Models\frais;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FraisController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'frais';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new frais());

        $grid->column('id', __('Id'));
        $grid->column('nom_frais', __('Nom frais'));
        $grid->column('description', __('Description'));
        $grid->column('type_frais', __('Type frais'));
        $grid->column('montant', __('Montant'));
        $grid->column('id_user', __('Id user'));
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
        $show = new Show(frais::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom_frais', __('Nom frais'));
        $show->field('description', __('Description'));
        $show->field('type_frais', __('Type frais'));
        $show->field('montant', __('Montant'));
        $show->field('id_user', __('Id user'));
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
        $form = new Form(new frais());

        $form->text('nom_frais', __('Nom frais'));
        $form->text('description', __('Description'));
        $form->number('type_frais', __('Type frais'));
        $form->decimal('montant', __('Montant'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}

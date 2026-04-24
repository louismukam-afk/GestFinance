<?php

namespace App\Admin\Controllers;

use App\Models\niveau;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class NiveauController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'niveau';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new niveau());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('nom_niveau', __('Nom niveau'));
        $grid->column('code_niveau', __('Code niveau'));
        $grid->column('id_user', __('Id user'));
        $grid->column('id_cycle', __('Id cycle'));

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
        $show = new Show(niveau::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('nom_niveau', __('Nom niveau'));
        $show->field('code_niveau', __('Code niveau'));
        $show->field('id_user', __('Id user'));
        $show->field('id_cycle', __('Id cycle'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new niveau());

        $form->text('nom_niveau', __('Nom niveau'));
        $form->text('code_niveau', __('Code niveau'));
        $form->number('id_user', __('Id user'));
        $form->number('id_cycle', __('Id cycle'));

        return $form;
    }
}

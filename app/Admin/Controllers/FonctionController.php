<?php

namespace App\Admin\Controllers;

use App\Models\fonction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FonctionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'fonction';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new fonction());

        $grid->column('id', __('Id'));
        $grid->column('nom_fonction', __('Nom fonction'));
        $grid->column('description', __('Description'));
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
        $show = new Show(fonction::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom_fonction', __('Nom fonction'));
        $show->field('description', __('Description'));
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
        $form = new Form(new fonction());

        $form->text('nom_fonction', __('Nom fonction'));
        $form->text('description', __('Description'));
        $form->number('id_user', __('Id user'));

        return $form;
    }
}

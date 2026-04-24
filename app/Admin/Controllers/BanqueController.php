<?php

namespace App\Admin\Controllers;

use App\Models\banque;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class BanqueController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'banque';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
   /* protected function grid()
    {
        $grid = new Grid(new banque());

        $grid->column('id', __('Id'));
        $grid->column('nom_banque', __('Nom banque'));
        $grid->column('telephone', __('Telephone'));
        $grid->column('localisation', __('Localisation'));
        $grid->column('code', __('Code'));
        $grid->column('description', __('Description'));
        $grid->column('email', __('Email'));
        $grid->column('id', __('Id user'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }*/
    protected function grid()
    {
        $grid = new Grid(new banque());
        $grid->model()->with('users');
        $grid->column('id', __('Id'));
        $grid->column('nom_banque', __('Nom banque'));
        $grid->column('telephone', __('Telephone'));
        $grid->column('localisation', __('Localisation'));
        $grid->column('code', __('Code'));
        $grid->column('description', __('Description'));
        $grid->column('email', __('Email'));
        $grid->column('users.name', __('Utilisateur')); // Affiche le nom de l'utilisateur
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
        $show = new Show(banque::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nom_banque', __('Nom banque'));
        $show->field('telephone', __('Telephone'));
        $show->field('localisation', __('Localisation'));
        $show->field('code', __('Code'));
        $show->field('description', __('Description'));
        $show->field('email', __('Email'));
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
        $form = new Form(new banque());

        $form->text('nom_banque', __('Nom banque'));
        $form->text('telephone', __('Telephone'));
        $form->text('localisation', __('Localisation'));
        $form->text('code', __('Code'));
        $form->text('description', __('Description'));
        $form->email('email', __('Email'));

        // Ici on cache le champ à l'utilisateur et on injecte la valeur plus tard
        $form->hidden('id_user');

        // Définir la valeur automatiquement lors de la sauvegarde
        $form->saving(function (Form $form) {
            $form->id_user = \Admin::user()->id; // ou Auth::id() si hors de Laravel Admin
        });

        return $form;
    }

   /* protected function form()
    {
        $form = new Form(new banque());

      /*  $ga = ['0'=>'selectionner l\'utilisateur'];
        $users=User::all();
        dump($users);
        die();
        foreach ($users as $gal){
            $ga[$users->id] = $gal->username;
        }
       // $user=\Auth::user()->id;
        $form->text('nom_banque', __('Nom banque'));
        $form->text('telephone', __('Telephone'));
        $form->text('localisation', __('Localisation'));
        $form->text('code', __('Code'));
        $form->text('description', __('Description'));
        $form->email('email', __('Email'));
       // $form->select('id_user', __('Utilisteur'))->options($ga);
        $form->number('id_user', __('Id user'));

        return $form;
    }*/
}

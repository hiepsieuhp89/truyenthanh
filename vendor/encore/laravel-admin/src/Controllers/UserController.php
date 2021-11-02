<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;
use Str;
use App\Area;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.administrator');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userModel = config('admin.database.users_model');
        

        $grid = new Grid(new $userModel());
        $grid->filter(function ($filter) {
            $filter->expand();
            $roleModel = config('admin.database.roles_model');
            $filter->disableIdFilter();

            $filter->equal('roles.id', 'Chức vụ')
            ->select(
                (new $roleModel())->pluck('name','id')
            );

            $filter->like('username', 'Tên Tài khoản');
        });

        $grid->model()->orderBy('id', 'DESC');

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', trans('admin.username'));
        $grid->column('name', trans('admin.name'));
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();

        $grid->column('areaId', trans('admin.area'))->display(function($value){

            $area = Area::where('id',explode(',',$value)[0])->first();

            return $area? $area->title : "";

        })->label(' label-primary')->style('font-size:16px;');

        $grid->column('stream_key', trans('admin.streamKey'));

        $grid->column('created_at', trans('admin.created_at'))->hide();

        $grid->column('updated_at', trans('admin.updated_at'))->hide();

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');

        $user = $userModel::findOrFail($id);

        $show = new Show($user);

        $show->field('id', 'ID');
        $show->field('username', trans('admin.username'));
        $show->field('name', trans('admin.name'));

        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        
        $show->field(trans('admin.permissions'))->as(function () use ($user){
            $r = $user->roles;
            $roleModel = config('admin.database.roles_model');
            return $roleModel::findOrFail($r[0]->id)->permissions->pluck('name');
        })->label();
        $show->field('stream_key', trans('admin.streamKey'));
        $show->field('area.title', trans('admin.area'));
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->display('id', 'ID');
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'))->rules('required');

        //$form->text('stream_key', trans('admin.streamKey'));

        $form->select('areaId', trans('admin.area'))->options(Area::selectOptions())->rules('required');

        $states = [
            'off' => ['value' => 0, 'text' => 'Không', 'color' => 'danger'], 
            'on' => ['value' => 1, 'text' => 'Có', 'color' => 'success'],
        ];

        $form->switch('stream_key', 'Cấp quyền phát trực tiếp')->states($states)->default(1);

        // $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', trans('admin.created_at'));
        
        $form->display('updated_at', trans('admin.updated_at'));

        $form->saving(function (Form $form) {

            $form->areaId = $this->findArea($form->areaId, $form->areaId);

            if ($form->password && $form->model()->password != $form->password) {

                $form->password = Hash::make($form->password);

            }
        });
        $form->saved(function (Form $form) {

            if($form->model()->stream_key){
                //not admin
                if (!$form->model()->can('*') && $form->areaId != 0) {

                    $area = Area::find($form->areaId);

                    $form->model()->stream_key = str_slug($area->title . '-' . $form->model()->username);
                }
                //is admin
                if ($form->model()->can('*'))
                    $form->model()->stream_key = str_slug('ad-' . $form->model()->username);

                $form->model()->stream_url = env('APP_STREAM_URL') . $form->model()->stream_key . '.m3u8';

                $form->model()->save();
            }
            else {
                $form->model()->stream_key = '';
                $form->model()->save();
            }      
        });

        return $form;
    }
    public function findArea($id, $result){

        $child_areas = Area::where('parent_id', $id)->get();

        if($child_areas !== NULL){
       
            foreach($child_areas as $ca){

                $result .= ','.$ca->id;

                $result = $this->findArea($ca->id, $result);

            }

        }

        return $result;
    }
}

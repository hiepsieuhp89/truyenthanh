<?php

namespace App\Admin\Controllers;

use App\Area;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
// use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class AreaController extends AdminController
{

    // use HasResourceActions;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    // public function store()
    // {
    //     Log::info('Showing STORE DATA ');
    //     return $this->form()->store();
    // }
    function __construct()
    {
        $this->title = trans('admin.deviceArea');
    }
        /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        Log::info('Showing INDEX TREE FILE ');

        return $content
            ->title(trans('Cụm loa'))
            ->description(trans('Danh sách các cụm loa theo khu vực'))
            ->row(function (Row $row) {
                $row->column(6, $this->treeView()->render());
                $row->column(6, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $form->action(admin_url('areas'));
                    $menuModel = new Area();

                    // $form = new Form($menuModel);
                    // $form = new Form(new Area);

                    // $menuModel = config('admin.database.menu_model');
                    // $menuModel = new Grid(new Area);

                    // $permissionModel = config('admin.database.permissions_model');
                    // $roleModel = config('admin.database.roles_model');

                    // $form->icon('icon', trans('admin.icon'))->default('fa-bars')->rules('required')->help($this->iconHelp());
                    // $form->text('uri', trans('admin.uri'));
                    // $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
                    // if ((new $menuModel())->withPermission()) {
                    //     $form->select('permission', trans('admin.permission'))->options($permissionModel::pluck('name', 'slug'));
                    // }

                    $form->select('parent_id', trans('admin.area'))->options($menuModel::selectOptions());
                    $form->text('title', trans('Cụm loa'))->rules('required');
                    $form->text('address', trans('admin.address'))->rules('required');
                    $form->latlong('lat', 'lon', 'Vị trí')->height(300)->default(['lat' => 21.0277644, 'lng' => 105.8341598]);
                    $form->select('api_status','Bật api')->options([
                        1 => "Có",
                        0 => "Không"
                    ])->default(0);
                    // $form->hidden('_token')->default(csrf_token());

                    $form->hidden('order')->default(0);
                    
                    
                    // $form->disableReset();
                    // $form->disableViewCheck();
                    // $form->disableEditingCheck();
                    // $form->disableCreatingCheck();
                    $column->append((new Box(trans('admin.new'), $form))->style('success'));
                });
            });
    }
   /**
     * @return \Encore\Admin\Tree
     */
    protected function treeView()
    {
        // $menuModel = config('admin.database.menu_model');
        // $menuModel = config('admin.database.menu_model');
        $dataModel = new Area();

        return $dataModel::tree(function (Tree $tree) {

            $tree->disableCreate();
            
            $tree->branch(function ($branch) {
                $payload = "<i class='fa-home'></i>&nbsp;<strong>{$branch['id']} - {$branch['title']}</strong>";

                // if (!isset($branch['children'])) {
                //     if (url()->isValidUrl($branch['uri'])) {
                //         $uri = $branch['uri'];
                //     } else {
                //         $uri = admin_url($branch['uri']);
                //     }

                //     $payload .= "&nbsp;&nbsp;&nbsp;<a href=\"$uri\" class=\"dd-nodrag\">$uri</a>";
                // }

                return $payload;
            });
        });
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Area);
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
        Log::info('Showing DETAIL DATA ');

        $show = new Show(Area::findOrFail($id));
        $show->field('id', trans('entity.id'));
        $show->field('title', trans('Cụm loa'));
        $show->field('address', trans('Địa chỉ'));
        $show->field('lat', trans('Tọa độ lat'));
        $show->field('lon', trans('Tọa độ lon'));
        $show->field('created_at', trans('entity.created_at'));
        $show->field('updated_at', trans('entity.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        Log::info('Showing FORM DATA ');
        $menuModel = new Area();
        $form = new Form($menuModel);
        // $form->number('parent_id', trans('admin.parent_id'));
        $form->select('parent_id', trans('Khu vực'))->options($menuModel::selectOptions());
        $form->text('title', trans('Cụm loa'));    
        $form->text('address', trans('Địa chỉ'));
        $form->latlong('lat', 'lon', 'Vị trí')->height(400)->default(['lat' => 21.0277644, 'lng' => 105.8341598]);
        $form->hidden('order', trans('order'));   
        $form->select('api_status','Bật api')
        ->options([
            1 => "Có",
            0 => "Không"
        ])->default(0);

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        
        return $form;
    }
}

<?php
/*
 * Plugin Name: Calc-Oil
 * Plugin URI: https://github.com/SnakeU2/calc-oil.git
 * Description:  Calculator for A. Povergo
 * Version:      0.1.0
 * Author:       Alexey M. Abrosimov (snakeu2@gmail.com) 
 * 
 */

/*----------------------Activation & Uninstall-----------------*/
register_activation_hook( __FILE__, function() use ($wpdb){
    //Check installed
    if (!is_admin() || (int)get_option('Calc_Oil_Installed' === 1)) return false;
    //Creating tables & insert data
    if(is_admin()){
        if(!is_file(__DIR__ . '/calc-oil.sql')){
            trigger_error('Отсутствует файл начальных данных calc-oil.sql<br>Пожалуйста, переустановите плагин', E_USER_ERROR);
        }
        $queries = file(__DIR__ . '/calc-oil.sql');
        foreach($queries as $query){
            $query = str_replace("wp_co_",$wpdb->prefix."co_");
            $wpdb->query($query);
        }
        add_option('Calc_Oil_Installed',1);
    }
});

/*--------------------Admin section----------------------*/

add_action('admin_enqueue_scripts', function (){
    //css
    wp_register_style('bootstrap',plugins_url('/bootstrap/css/bootstrap.css',__FILE__));
    wp_enqueue_style('bootstrap');
    wp_enqueue_style( 'co-admin-style', plugins_url('/admin_style.css',__FILE__));
    //js register   
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');	
    wp_register_script( 'popper',plugins_url('/bootstrap/js/popper.min.js',__FILE__),array('jquery'));
    wp_register_script( 'bootstrap', plugins_url('/bootstrap/js/bootstrap.min.js',__FILE__),array('jquery'));
    wp_register_script( 'co_admin-script', plugins_url('/js/co_admin.js',__FILE__),array('jquery'));
    //js enqueue
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'popper' );
	wp_enqueue_script( 'bootstrap' );
	wp_enqueue_script( 'co_admin-script' );
    
});

add_action('admin_menu', function (){
        add_menu_page( 'Таблица масел', 'Calc-Oil', 'manage_options', 'calc-oils', 'get_oils_list',"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAQOFAAEDhQGlVDz+AAAAB3RJTUUH4gEDChQt3muYgQAAAg1JREFUSMet1TtolEEQB/Dfd3lhDJhoIeIDQRQCQhKCigpaiSgGrYIKaYJp7FOkFcRHK6SIWGiTyk5ETLQRrAVNo4WgEiNCHkIkp+bGZqNfjot38TIw7M7szvx3/zvMZv4t27ADWU5XpIRlvMPPtRI0VgE4j3t4g09oSP4izmIJuzCnDnmDh2nekrQ9Je2zAXIdr3P2ZnThVy3BjTXsKZXZL9MtGjYSIP+4l7ETjxNQsV6AZRRy9hRm8AObNgLgV9kNzmMLmtCK+XoBlsr4vp+oaURzteBCDQAnyuq8PVEzjTP1lugFBPZVWDuV1vb/b/I2fMG5Mn8TruBmWn+QfOuW23hSgedm3MJzPE1vdHxdmQsFpxGjo65GOJz0UITeCD0RDkTYG2H30JBrzc0WU2VVl4YGW/FhdFQpwrcIC0nnczoXYa6vz2x3t4UsE3hR6wXuHD0qIqrryIjo7xeDg6K1VRGXqiU/2dEhpqerJy+VVtsTEyLLvE3NcE3uv9+4Udvp80Dd3WL7dpHKdmwV5bn53c5OR8bH11dqWcbyMl1dHDvGq1d6i0WT+Jjf14KvExPrO30lHR4WqSGukoOYnZ+vH2BqSqRu257vRe+xNDlZ//f37BlYXOmyWY7LoaYmYwMDtLUR8ZfjSrxX8s3MMD5usVBwsVTySFmfhz3oSX3oT2yF+Vrj5/R/T68E/AZ5P2dgMqLZNQAAAABJRU5ErkJggg==","21.1" );
});


function get_oils_list() { //oils (main) admin page 
    global $wpdb;
    $oils = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix . 'co_oils');
    ?>
    <div class="container-fluide">
        <h2><?php echo get_admin_page_title() ?></h2>
        <nav class="navbar navbar-light bg-light justify-content-between">
          <button id="do-ajax" class="btn btn-outline-success" type="button">Main button</button>
          <form class="form-inline">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" >Search</button>
          </form>
        </nav>
        <table class="table  table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Группа</th>
                    <th>Йод</th>
                    <th></th>
                </tr>
            </thead>
            <tbody> 
                <?php foreach ($oils as $oil): ?>
                <tr id="row_<?php echo $oil->id; ?>">
                    <td td-data="id"><?php echo $oil->id; ?></td>
                    <td td-data="name"><?php echo $oil->name; ?></td>
                    <td td-data="group"><?php echo $oil->o_group; ?></td>
                    <td td-data="iodine"><?php echo $oil->iodine; ?></td>
                    <td><button class="btn btn-success oil-edit" btn-data="<?php echo $oil->id; ?>">Редактировать</td>
                </tr>
                <?php endforeach; ?>
            </tbody>            
        </table>
        <!--acids modal-->
        <div class="modal" id="co_oils_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row align-items-center">
                            <div class="col-12">
                                <input id="co_oil_name" value="" class="form-control" placeholder="Масло...">
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                            <div class="col-auto">
                                <label class="mr-sm-2" for="co_oil_group">Группа</label>
                                <select class="form-control custom-select mr-sm-2" id="co_oil_group">                                    
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="mr-sm-2" for="co_oil_iodine">Йод</label>
                                <input  class="form-control" type="text" id="co_oil_iodine">                                    
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                            <table class="table table-striped" id="co_acids_table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Наименование</th>
                                        <th>%</th>                                        
                                    </tr>
                                </thead>                                
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary">Cохранить</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end acids modal-->
    </div>
    
<?php
} //end get_oils_list

add_action('wp_ajax_get_oils', function() use ($wpdb){ //get oils as json object
    $oils = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."co_oils`");
    foreach ($oils as &$oil){        
        $oil->acids = $wpdb->get_results("SELECT id_acid as id, percent FROM `".$wpdb->prefix."co_oils_acids` WHERE id_oil ='".$oil->id."'");
    }

    $query = "SELECT * FROM `".$wpdb->prefix."co_acids`";
    $acids = $wpdb->get_results($query);
    
    $query = "SELECT COLUMN_TYPE 
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = '".$wpdb->dbname."'
             AND TABLE_NAME = '".$wpdb->prefix."co_oils'
             AND COLUMN_NAME = 'o_group'";
    $groups = $wpdb->get_var($query);
    $groups = explode(",",str_replace("'","",str_replace(")","",str_replace("enum(","",strtolower($groups)))));

    $query = "SELECT COLUMN_TYPE 
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = '".$wpdb->dbname."'
             AND TABLE_NAME = '".$wpdb->prefix."co_acids'
             AND COLUMN_NAME = 'type'";
    $types = $wpdb->get_var($query);
    $types = explode(",",str_replace("'","",str_replace(")","",str_replace("enum(","",strtolower($types)))));    

    echo json_encode(array('nonce'=>wp_create_nonce('oc-adm-ajax-nonce'),'oils'=>$oils,'groups'=>$groups, 'types'=>$types, 'acids'=>$acids));
    wp_die();
});

/*--------------------------frontend-----------------------*/

 /*  
  * TODO: pagination in admin table
  * TODO: float form for edit oil
  * TODO: filters in admin table
  * TODO: edit acids list
  * TODO: output
  * TODO: shortcode
  * TODO: widget
  *
  * what I know about git commands:
  * 1. git init - startin git work, create master branch
  * 2. git add <file1>..<fileN> - add files in current dir to local git repo. Use mask.
  * 3. git status - show not committed|changed|not addedd files
  * 4. git branch (-a) - show all local branches. -a - all branches w. remote
  * 5. git commit -am "comment" - fix changes in branch -m comment -a - add|remove|change file structure in branch
  * 6. git checkout <branch> - switch to branch,q replace all files from current branch commit. Warn! may be lost all latest modifs in old branch? if not commit it
  * 7. git reflog - all actions !Important to see wich branch|commit now worked
  * 8. git reset (soft) [--hard] <commit ID> - move head to <commit ID> with --hard change files in woring dir. Not safe!
  * 9. git reset HEAD@{<num>} - see in reflog actions and choose one of them.
  * 10.git merge [-ff] <branch> - merge currnt head with <branch> -ff means fastforward, just move cursot to curren commin in branch
  * 
  * /

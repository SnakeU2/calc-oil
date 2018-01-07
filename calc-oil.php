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



add_action('admin_enqueue_scripts', function ($hook){
    if($hook != "toplevel_page_calc-oils") return;
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

include(__DIR__ . '/widget.php');

function get_oils_list() { //oils (main) admin page 
    global $wpdb;
    $where_c = ($_GET['search'])?" WHERE `name` LIKE '%".$_GET['search']."%' ":" ";
    //pagination
    $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $limit = 25; // number of rows in page
    $offset = ( $pagenum - 1 ) * $limit;
    $total = $wpdb->get_var( "SELECT COUNT(`id`) FROM {$wpdb->prefix}co_oils".$where_c );
    $num_of_pages = ceil( $total / $limit );
    $page_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'text-domain' ),
            'next_text' => __( '&raquo;', 'text-domain' ),
            'total' => $num_of_pages,
            'current' => $pagenum
            
        ) );
    $query = 'SELECT * FROM '.$wpdb->prefix . 'co_oils '.$where_c.' LIMIT '.$offset.", ".$limit;
    $oils = $wpdb->get_results($query);
    
    ?>
    <div class="container-fluide">
        <h2><?php echo get_admin_page_title() ?></h2>       
        <nav class="navbar navbar-light bg-light justify-content-between">          
          <form class="form-inline" method="GET">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="search" value="<?php echo $_GET['search'];?>">
            <input type="hidden" name="page" value="calc-oils">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Поиск</button>
          </form>
          <button class="btn btn-primary" id="add-oil">Добавить масло</button>
        </nav>
        <table class="table  table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Наименование</th>
                    <th>Группа</th>
                    <th>Йодное число</th>
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
        <?php
        if ( $page_links) : $paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');?>
        <div class="container"><div class="d-flex justify-content-center">
            <div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">
            <?php echo $page_links; ?>
            </div></div>
        </div></div>
        <?php endif; ?>
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
                        <div class="form-group row align-items-center">
                            <div class="col-12">
                                <input id="co_oil_name" value="" class="form-control" placeholder="Масло...">
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <div class="col-auto">
                                <label class="mr-sm-2" for="co_oil_group">Группа</label>
                                <select class="form-control custom-select mr-sm-2" id="co_oil_group">                                    
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="mr-sm-2" for="co_oil_iodine">Йодное число</label>
                                <input class="form-control" type="text" id="co_oil_iodine">                                                                   
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <table class="table table-striped" id="co_acids_table">
                                <thead>
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
                        <button type="button" id="btn-save-oil" class="btn btn-primary">Cохранить</button>
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
        $oil->acids = $wpdb->get_results("SELECT id_acid as id, ROUND(percent,2) as percent FROM `".$wpdb->prefix."co_oils_acids` WHERE id_oil ='".$oil->id."'");
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

add_action('wp_ajax_update_oil', function() use ($wpdb){
    check_ajax_referer( 'oc-adm-ajax-nonce', 'nonce');
    $oil = (gettype($_POST['oil']) === "string")?@json_decode(stripcslashes($_POST['oil'])):null;
    //catch json err
    if(is_null($oil)){       
        $json_err = (json_last_error() !== JSON_ERROR_NONE)?"JSON error: ".json_last_error_msg():"";
        $retStr = "Something wrong ".$json_err;
        $oilStr =  $_POST['oil'];
        echo json_encode(array('msg'=>$retStr,'oil'=>$oilStr));
        wp_die();
    }

    //update co_oils
    $id = ((int)$oil->id === -1)?'':$oil->id;
    //$query = "INSERT INTO ".$wpdb->prefix."co_oils (id, name, iodine, o_group) VALUES('".implode("','",array($id, $oil->name, $oil->iodine, $oil->o_group))."') ON DUPLICATE KEY UPDATE  name='".$oil->name."', iodine='".$oil->iodine."', o_group='".$oil->o_group."'";
    $query  = "INSERT INTO {$wpdb->prefix}co_oils (id,name,o_group,iodine) VALUES (%d,%s,%s,%d) ON DUPLICATE KEY UPDATE name = %s, o_group=%s, iodine=%d";
    $query = $wpdb->prepare($query, $id, $oil->name, $oil->o_group, $oil->iodine, $oil->name, $oil->o_group, $oil->iodine);

    //$wpdb->query($query);
    //update co_oils_acids
    

    echo json_encode(array('msg'=>'OK','oil'=>$oil,'query'=>$query));
    wp_die();
});

/*--------------------------frontend-----------------------*/

 /*  
  * TODO: save oil
  * TODO: edit acids list  
  * TODO: shortcode
  * TODO: widget
  * TODO: enqueue frontend js with calc_oil
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
  * 11.git push [-f] <repo name> <remote branch> -  write to remote repository. -f - force
  * 12.git remote add <name> <remote url> - add repo. default name - origin
  * 13.git pull <repo name> <remote branch> - load all from remote repo.
  * 14. git show-branch -a - view all branches
  * /

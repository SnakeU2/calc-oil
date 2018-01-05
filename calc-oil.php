<?php
/*
 * Plugin Name: Calc-Oil
 * Plugin URI: https://github.com/SnakeU2/calc-oil.git
 * Description:  Calculator for A. Povergo
 * Version:      0.1.0
 * Author:       Alexey M. Abrosimov (snakeu2@gmail.com) 
 * 
 */

/*----------------------Activation------------------*/
    register_activation_hook( __FILE__, 'co_install');

    function co_install(){
        global $wpdb;
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
        
    }

/*--------------------Admin section----------------------*/

add_action('admin_enqueue_scripts', 'calc_oil_adm_scripts');

add_action('admin_menu', function (){
        add_menu_page( 'Таблица масел', 'Calc-Oil', 'manage_options', 'calc-oils', 'get_oils',"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAQOFAAEDhQGlVDz+AAAAB3RJTUUH4gEDChQt3muYgQAAAg1JREFUSMet1TtolEEQB/Dfd3lhDJhoIeIDQRQCQhKCigpaiSgGrYIKaYJp7FOkFcRHK6SIWGiTyk5ETLQRrAVNo4WgEiNCHkIkp+bGZqNfjot38TIw7M7szvx3/zvMZv4t27ADWU5XpIRlvMPPtRI0VgE4j3t4g09oSP4izmIJuzCnDnmDh2nekrQ9Je2zAXIdr3P2ZnThVy3BjTXsKZXZL9MtGjYSIP+4l7ETjxNQsV6AZRRy9hRm8AObNgLgV9kNzmMLmtCK+XoBlsr4vp+oaURzteBCDQAnyuq8PVEzjTP1lugFBPZVWDuV1vb/b/I2fMG5Mn8TruBmWn+QfOuW23hSgedm3MJzPE1vdHxdmQsFpxGjo65GOJz0UITeCD0RDkTYG2H30JBrzc0WU2VVl4YGW/FhdFQpwrcIC0nnczoXYa6vz2x3t4UsE3hR6wXuHD0qIqrryIjo7xeDg6K1VRGXqiU/2dEhpqerJy+VVtsTEyLLvE3NcE3uv9+4Udvp80Dd3WL7dpHKdmwV5bn53c5OR8bH11dqWcbyMl1dHDvGq1d6i0WT+Jjf14KvExPrO30lHR4WqSGukoOYnZ+vH2BqSqRu257vRe+xNDlZ//f37BlYXOmyWY7LoaYmYwMDtLUR8ZfjSrxX8s3MMD5usVBwsVTySFmfhz3oSX3oT2yF+Vrj5/R/T68E/AZ5P2dgMqLZNQAAAABJRU5ErkJggg==","21.1" );
});


function get_oils() {
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
                <tr>
                    <td><?php echo $oil->id; ?></td>
                    <td><?php echo $oil->name; ?></td>
                    <td><?php echo $oil->group; ?></td>
                    <td><?php echo $oil->iodine; ?></td>
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
                            <div class="col-auto">
                                <input name=" " value="" placeholder="Масло...">
                            </div>                            
                            <div class="col-auto">
                                <label class="mr-sm-2" for="inlineFormCustomSelect">Группа</label>
                                <select class="custom-select mr-sm-2" id="inlineFormCustomSelect">
                                    <option value="b0">b0</option>
                                    <option value="pf1">pf1</option>
                                    <option value="pf2">pf2</option>
                                    <option value="b1">b1</option>
                                    <option value="b2">b2</option>
                                    <option value="b3">b3</option>
                                    <option value="a1">a1</option>
                                    <option value="a2">a2</option>
                                    <option value="a3">a3</option>
                                    <option value="z">z</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                            <table class="table table-striped ">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Наименование</th>
                                        <th>%</th>                                        
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
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

}

function calc_oil_adm_scripts(){
    wp_register_style('bootstrap',plugins_url('/bootstrap/css/bootstrap.css',__FILE__));
    wp_enqueue_style('bootstrap');
    wp_enqueue_style( 'co-admin-style', plugins_url('/admin_style.css',__FILE__));
    //js
    
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');	
    wp_register_script( 'popper',plugins_url('/bootstrap/js/popper.min.js',__FILE__),array('jquery'));
    wp_register_script( 'bootstrap', plugins_url('/bootstrap/js/bootstrap.min.js',__FILE__),array('jquery'));
    wp_register_script( 'co_admin-script', plugins_url('/js/co_admin.js',__FILE__),array('jquery'));
  
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'popper' );
	wp_enqueue_script( 'bootstrap' );
	wp_enqueue_script( 'co_admin-script' );
    wp_localize_script('co_admin-script', 'co_admin_ajax', 
		array(			
			'nonce' => wp_create_nonce('oc-adm-ajax-nonce')
		)
	);

}

add_action('wp_ajax_restruct_tabs', function(){
   
    echo json_encode("Something done. But result?");
    wp_die();
});
add_action('wp_ajax_get_oil', function() use ($wpdb){
    check_ajax_referer( 'oc-adm-ajax-nonce', 'nonce' );
    $id = intval( $_POST['oil_id'] );
    $res = $wpdb->get_results("SELECT id, `name` as acid, a.`type`, 0 as percent FROM `wp_co_acids` as `a` 
                                WHERE a.id NOT IN (SELECT id_acid FROM `wp_co_oils_acids` as oa WHERE oa.id_oil =".$id.")
                                UNION
                                SELECT id_acid as id, `name`as acid, a.`type`, ROUND(`percent`,2) FROM `wp_co_acids` as `a` 
                                LEFT JOIN `wp_co_oils_acids` as oa ON `a`.id = oa.id_acid
                                WHERE oa.id_oil =".$id." ORDER BY `type`,id");
    echo json_encode($res);
    wp_die();
});


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

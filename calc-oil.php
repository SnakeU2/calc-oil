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
                $wpdb->query($query);
            }
            add_option('Calc_Oil_Installed',1);           
        }        
        
    }

/*--------------------Admin section----------------------*/
 
 

 /*  
  * TODO: remove hook - remove tables  
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

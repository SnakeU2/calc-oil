<?php
/*
 * Plugin Name: Calc-Oil
 * Plugin URI: https://github.com/SnakeU2/calc-oil.git
 * Description:  Calculator for A. Povergo
 * Version:      0.1.0
 * Author:       Alexey M. Abrosimov (snakeu2@gmail.com) 
 * 
 */

function parse_oils(){
    $csv = array_map('str_getcsv', file(__DIR__.'/oils.csv'));
    array_walk($csv, function(&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
        
    });
    array_shift($csv); # remove column header
    var_dump($csv);

}

parse_oils();
 

 /*
  * TODO: activate hook - creating tables if not exists
  * TODO: remove hook - remove tables
  * TODO: tables data  
  * TODO: output
  * TODO: shortcode
  * TODO: widget
  * /

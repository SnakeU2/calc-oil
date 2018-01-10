<?php
class CO_Widget extends WP_Widget {
	
	function __construct() {
		
		// __construct( $id_base, $name, $widget_options = array(), $control_options = array() )
		parent::__construct(
			'co_widget', 
			'Калькулятор',
			array( 'description' => 'Калькулятор масел', /*'classname' => 'my_widget',*/ )
		);

		// скрипты/стили виджета, только если он активен
		if ( is_active_widget( false, false, $this->id_base ) || is_customize_preview() ) {
			add_action('wp_enqueue_scripts', array( $this, 'add_scripts' ));
			add_action('wp_head', array( $this, 'add_style' ) );
		}
	}

	/**
	 * Вывод виджета во Фронт-энде
	 *
	 * @param array $args     аргументы виджета.
	 * @param array $instance сохраненные данные из настроек
	 */
	function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		?>

        <div class="container-fluide">
            <div class="flex-column">
                <nav class="navbar navbar-light bg-light flex-row justify-content-between align-items-center">
                <?php  if ( ! empty( $title ) ) {
                    echo $args['before_title'] . $title . $args['after_title'];
                } ?>
                  <form class="form-inline">                    
                    <button class="btn btn-sm align-middle btn-outline-primary" type="button" id="co_open_choise">Добавить</button>
                  </form>
                </nav>
                <div id="calc-tab">
                    <table class="table" id="co_calc_oil_table">
                        <thead>
                            <tr>                               
                                <th>Наименование</th>
                                <th>% в смеси</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="calc-info">
                    <div id="info-count"><h6 class="p-1"><span class="t-blue d-none" ><i class="fa fa-info-circle"></i></span>&nbsp;Кол-во масел в смеси</h6><div class="status pink p-1">0% - Нужно добавить масел!</div></div>
                    <div id="info-acid-potencial"><h6 class="p-1" ><span class="t-blue d-none" ><i class="fa fa-info-circle"></i></span>&nbsp;Потенциал окисления</h6><div class="status p-1"></div></div>
                    <div id="info-liquid"><h6 class="p-1"><span class="t-blue" tooltip="Норма:&#xA;Невысыхающие: 50%&#xA;Полувысыхающие: 35%&#xA;Высыхающие: 15%"><i class="fa fa-info-circle"></i></span>&nbsp;Растекаемость масел</h6><div class="status p-1"></div></div>
                    <div id="info-olein-linol"><h6 class="p-1"><span class="t-blue d-none" ><i class="fa fa-info-circle"></i></span>&nbsp;Олеиновая/линолевая</h6><div class="status p-1"></div></div>
                    <div id="info-linol-lionlen"><h6 class="p-1"><span class="t-blue d-none" ><i class="fa fa-info-circle"></i></span>&nbsp;Линолевая/линоленовая</h6><div class="status p-1"></div></div>
                    <div id="info-palmitine"><h6 class="p-1"><span class="t-blue d-none" ><i class="fa fa-info-circle"></i></span>&nbsp;Пальмитиновая</h6><div class="status p-1"></div></div>
                </div>
            </div>
        </div>
        <!--- modal form-->
         <div class="modal" id="co_oils_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Подбор масел</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="form-group row align-items-end justify-content-between">
                            <div class="col-auto">
                                <label class="mr-sm-2" for="co_oil_group">Группа</label>
                                <select class="form-control custom-select mr-sm-2" id="co_oil_group">                                    
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="button" id="btn-choose-oil" class="btn btn-primary btn-choose-oil">Выбрать</button>
                            </div>                           
                        </div>
                        <div class="container-fluide align-items-center">
                            <table class="table table-striped" id="co_choise_oil_table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Наименование</th>
                                        <th>Йодное число</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-wrap" id="choise-acids"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="btn-choose-oil" class="btn btn-primary btn-choose-oil">Выбрать</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>   
                
	<?php echo $args['after_widget'];
	}

	/**
	 * Админ-часть виджета
	 *
	 * @param array $instance сохраненные данные из настроек
	 */
	function form( $instance ) {
		$title = @ $instance['title'] ?: 'Заголовок по умолчанию';

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Сохранение настроек виджета. Здесь данные должны быть очищены и возвращены для сохранения их в базу данных.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance новые настройки
	 * @param array $old_instance предыдущие настройки
	 *
	 * @return array данные которые будут сохранены
	 */
	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

	// скрипт виджета
	function add_scripts() {
		// фильтр чтобы можно было отключить скрипты
		if( ! apply_filters( 'show_calc_oil_script', true, $this->id_base ) )
			return;

         //js register   
        wp_deregister_script( 'jquery' );
        wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');        
        wp_register_script( 'popper',plugins_url('/bootstrap/js/popper.min.js',__FILE__),array('jquery'));        
        wp_register_script( 'bootstrap', plugins_url('/bootstrap/js/bootstrap.min.js',__FILE__),array('jquery'));        
        
        //js enqueue
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'popper' );
        wp_enqueue_script( 'bootstrap' );
		wp_enqueue_script('co_frontend_script', plugins_url('/js/co_frontend.js',__FILE__),array('jquery','bootstrap'));
        wp_localize_script( 'co_frontend_script', 'co_ajax', 
            array(
                'url' => admin_url('admin-ajax.php')
            )
        );  
	}

	// стили виджета
	function add_style() {
		// фильтр чтобы можно было отключить стили
		if( ! apply_filters( 'show_calc_oil_style', true, $this->id_base ) )
			return;
		wp_enqueue_style( 'co-admin-style', plugins_url('/css/widget.css',__FILE__));
	}

} 

add_action( 'widgets_init', function () {
	register_widget( 'CO_Widget' );
});

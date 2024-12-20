<?php 
if (!class_exists("TalentListings"))
{


class TalentListings
	extends WP_List_Table
{


	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We 
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	public function __construct()
	{
		global $status, $page;
	
		//Set parent defaults
		parent::__construct( array(
			'singular'  => 'talent-person',		// singular name of the listed records
			'plural'    => 'talent-people',		// plural name of the listed records
			'ajax'      => FALSE				// does this table support ajax?
		) );
	
	}
	
	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	//function column_cb( $item )
	//{
	//	return sprintf(
	//		'<input type="checkbox" name="%1$s[]" value="%2$s" />',
	//		/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
	//		/*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
	//	);
	//}
	
	
	/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	public function extra_tablenav( $which )
	{
		if ( $which == "top" ){
			//The code that goes before the table is here
			//echo"Hello, I'm before the table";
		}
		if ( $which == "bottom" ){
			//The code that goes after the table is there
			//echo"Hi, I'm after the table";
		}
	}
	 
	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value 
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 * 
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	public function get_columns()
	{
		$columns = array(
			'headshot_thumb'	=> __( 'Thumbnail' ),
			'name'				=> __( 'Name' ),
			'age'				=> __( 'Age' )
		);
		return( $columns );
	}
	 
	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
	 * you will need to register it here. This should return an array where the 
	 * key is the column that needs to be sortable, and the value is db column to 
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 * 
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 * 
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	public function get_sortable_columns()
	{
		$sortable_columns = array(
			'name'			=> array( 'name_last', FALSE ),
			'age'			=> array( 'birthdate', FALSE )
		);
		return( $sortable_columns );
	}
	
	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 * 
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 * 
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 * 
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	//public function get_bulk_actions()
	//{
	//	$actions = array(
	//		'delete'    => 'Delete'
	//	);
	//	return( $actions );
	//}
	
	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 * 
	 * @see $this->prepare_items()
	 **************************************************************************/
	//public function process_bulk_action()
	//{    
	//	//Detect when a bulk action is being triggered...
	//	if( 'delete' === $this->current_action() ) {
	//		wp_die('Items deleted (or they would be if we had items to delete)!');
	//	}
	//}
	 
	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 * 
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	public function prepare_items()
	{
		global $_wp_column_headers;
		$screen = get_current_screen();
		
		/**
		 * First, lets decide how many records per page to show
		 */
		$perPage = 20;
		
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		 $columns = $this->get_columns();
		 $hidden = array();
		 $sortable = $this->get_sortable_columns();
		 
		 /**
		  * REQUIRED. Finally, we build an array to be used by the class for column 
		  * headers. The $this->_column_headers property takes an array which contains
		  * 3 other arrays. One for all columns, one for hidden columns, and one
		  * for sortable columns.
		  */
		 $this->_column_headers = array($columns, $hidden, $sortable);
		 
		 /**
		  * Optional. You can handle your bulk actions however you see fit. In this
		  * case, we'll handle them within our package just to keep things clean.
		  */
		 //$this->process_bulk_action();
		 
		 /**
		  * Instead of querying a database, we're going to fetch the example data
		  * property we created for use in this plugin. This makes this example 
		  * package slightly different than one you might build on your own. In 
		  * this example, we'll be using array manipulation to sort and paginate 
		  * our data. In a real-world implementation, you will probably want to 
		  * use sort and pagination data to build a custom query instead, as you'll
		  * be able to use your precisely-queried data immediately.
		  */
		$_data_object = TalentPerson::initAll();
		$_data = ( $_data_object ) ? $_data_object->records : array( (object) array(
			'name_first'			=> 'No',
			'name_last'				=> 'Records',
			'image_headshot_thumb'	=> '',
			'birthdate'				=> date( 'Y-m-d' )
		) );
	
		/**
		 * This checks for sorting input and sorts the data in our array accordingly.
		 * 
		 * In a real-world situation involving a database, you would probably want 
		 * to handle sorting by passing the 'orderby' and 'order' values directly 
		 * to a custom query. The returned data will be pre-sorted, and this array
		 * sorting technique would be unnecessary.
		 */
		function usort_reorder( $a, $b )
		{
			$a = ( is_object( $a ) ) ? get_object_vars( $a ) : $a;
			$b = ( is_object( $b ) ) ? get_object_vars( $b ) : $b;
			$orderby = ( !empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id'; //If no sort, default to id
			$order = ( !empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
			$result = strcmp( $a[$orderby], $b[$orderby] ); //Determine sort order
			return ( $order === 'asc') ? $result : -$result; //Send final sort direction to usort
		}
		usort( $_data, 'usort_reorder' );
		
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently 
		 * looking at. We'll need this later, so you should always include it in 
		 * your own package classes.
		 */
		$currentPage = $this->get_pagenum();
		
		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array. 
		 * In real-world use, this would be the total number of items in your database, 
		 * without filtering. We'll need this later, so you should always include it 
		 * in your own package classes.
		 */
		$totalItems = count( $_data );
		//$totalItems = $_data_object->num_records;
		
		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to 
		 */
		$_data = array_slice( $_data, ( ( $currentPage - 1 ) * $perPage ), $perPage );
		
		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where 
		 * it can be used by the rest of the class.
		 */
		$this->items = $_data;
		
		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items'	=> $totalItems,						// WE have to calculate the total number of items
			'per_page'		=> $perPage,						// WE have to determine how many items to show on a page
			'total_pages'	=> ceil( $totalItems / $perPage )	// WE have to calculate the total number of pages
		) );
		
		/* -- Register the Columns -- */
		$_wp_column_headers[ $screen->id ] = $columns;
		
	}
	
	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title() 
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as 
	 * possible. 
	 * 
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 * 
	 * For more detailed insight into how columns are handled, take a look at 
	 * WP_List_Table::single_row_columns()
	 * 
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	public function column_default($item, $column_name)
	{
		switch( $column_name )
		{
			case 'foo':
			case 'bar':
			case 'email_address':
				return( $item->$column_name );
				break;
			default:
				//return( print_r( $item, TRUE ) ); //Show the whole array for troubleshooting purposes
				return( 'nothing found' );
		}
	}
	
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named 
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 * 
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 * 
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/

	public function column_headshot_thumb( $item )
	{
			$ratio = min( 115 / Talent::getOption('img_thumb_width'), 115 / Talent::getOption('img_thumb_height') );
			$width = $ratio * Talent::getOption('img_thumb_width');
			$height = $ratio * Talent::getOption('img_thumb_height');

			$style = "display: block; width: {$width}px; height: {$height}px; background: transparent url(" . Talent::$url->plugin . "images/person-icon.png) no-repeat center center; background-size: cover;";

			$thumbnail = ( !empty($item->image_headshot_thumb) && TalentDisplay::file_url_exists( TalentUpload::$upload_url . $item->image_headshot_thumb ) ) ? '<img src="' . TalentUpload::$upload_url . $item->image_headshot_thumb . '" width="' . $width . '" height="' . $height . '" />' : '';
			
			$html = '<div id="tcd-head-thumb" style="' . $style . '">' . $thumbnail . '</div>';
			return( $html );
	}

	public function column_name( $item )
	{
		$_name =  $item->name_first . ' ' . $item->name_last;
		// JS Confirmation Dialog
		$js_dialog = "if( confirm( 'You are about to delete this record \\'{$_name}\\'\\n \\'Cancel\\' to stop, \\'OK\\' to delete.' ) ) { return true; } return false;";
		
		// Build row actions
		$actions = array(
			'edit'		=> sprintf( '<a href="?page=%s&action=%s&person-id=%s">Edit</a>',	$_REQUEST['page'], 'edit', $item->id ),
			'delete'	=> sprintf( '<a href="?page=%s&action=%s&person-id=%s%s" onclick="%s" >Delete</a>',	$_REQUEST['page'], 'delete', $item->id, Talent::createURLNonce( 'delete person' ), $js_dialog ),
		);
		

		if( 'No Records' !== $_name )
		{
		//Return the title contents
			return( sprintf('%1$s %2$s',
				/*$1%s*/ $_name,
				/*$2%s*/ //$item->id,
				/*$3%s*/ $this->row_actions( $actions )
			) );
		} else {
			return( $_name );
		}
	}

	public function column_age( $item )
	{
		$parsed_birthdate = TalentPerson::parseAge( $item->birthdate );
		$age = ($item->use_birthdate) ? TalentPerson::parseAge($parsed_birthdate, 'age') : $item->age;
		return( $age );
	}

	public function column_password( $item )
	{
		//$_password = stripslashes( CollaborateSecurity::secure( $item->password, 'decrypt' ) );
		//$_dots = str_repeat('&bull;', 16);
		
		//$_html = '<span class="tcd-table-password" data-pass="' . htmlspecialchars( $_password ) . '" data-dots="' . $_dots . '" style="font-family: \'Lucida Console\', Monaco, monospace; letter-spacing: .1em;" >' . $_dots . '</span><input class="cas-show-hide-pass button-secondary" type="button" name="cas_reveal" value="Reveal" style="float:right;" />';
		//return( $_html );
	}
	
	public function column_user_name( $item )
	{
		return( $item->first_name . ' ' . $item->last_name );
	}
	
	public function column_login_name( $item )
	{
		// JS Confirmation Dialog
		$js_dialog = "if( confirm( 'You are about to delete this record \\'{$item->login_name}\\'\\n \\'Cancel\\' to stop, \\'OK\\' to delete.' ) ) { return true; } return false;";
		
		// Build row actions
		$actions = array(
			'edit'		=> sprintf( '<a href="?page=%s&action=%s&person-id=%s">Edit</a>',	$_REQUEST['page'], 'edit', $item->id ),
			'delete'	=> sprintf( '<a href="?page=%s&action=%s&person-id=%s%s" onclick="%s" >Delete</a>',	$_REQUEST['page'], 'delete', $item->id, Talent::createURLNonce( 'delete person' ), $js_dialog ),
		);
		
		//Return the title contents
		return( sprintf('%1$s %2$s',
			/*$1%s*/ $item->login_name,
			/*$2%s*/ //$item->id,
			/*$3%s*/ $this->row_actions( $actions )
		) );
	}	 
	 

} // end class
} // end if/exists

?>
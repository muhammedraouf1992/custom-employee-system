<?php 
class MyEmployee{

  
    private $wpdb;
    private $table_name;
    private $table_prefix;
    
    public function __construct(){
        global $wpdb;
        $this->wpdb=$wpdb;
        $this->table_prefix=$this->wpdb->prefix;
        $this->table_name=$this->table_prefix.'employees_table';
    }

    public function wce_on_plugin_activate(){

        $collate=$this->wpdb->get_charset_collate();

        $createCommand = "
            CREATE TABLE `".$this->table_name."` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `email` varchar(50) DEFAULT NULL,
            `designation` varchar(50) DEFAULT NULL,
            `profile_image` varchar(220) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ".$collate."
        ";
        require_once(ABSPATH."wp-admin/includes/upgrade.php");

        dbDelta($createCommand);

         // Wp Page
         $page_title = "Employee CRUD System Page";
         $page_content = "[wce-employee-layout]";
 
         if(!get_page_by_title($page_title)){
             wp_insert_post(array(
                 "post_title" => $page_title,
                 "post_content" => $page_content,
                 "post_type" => "page",
                 "post_status" => "publish"
             ));
         }
       
    }

    public function wce_delete_table(){
        $delete_command = "DROP TABLE IF EXISTS {$this->table_name}";

        $this->wpdb->query($delete_command); 

        return wp_send_json([
            "status" => true,
            "message" => "Employee Deleted Successfully"
        ]);
    }

    public function wce_add_employee_page(){
        ob_start();
        
        include_once WCE_DIR_PATH."template/crud-form.php";
        
        $template=ob_get_contents();
        ob_get_clean();

       
        return $template;
                
    }

    public function wce_add_assets(){
        wp_enqueue_style( 'crud-employee-style',WCE_DIR_URL."assets/style.css" );
        wp_enqueue_script( 'crud-template-script',WCE_DIR_URL."assets/script.js",array());
        wp_localize_script('crud-template-script',"wce_object",array('ajax_url'=>admin_url('admin-ajax.php'))  );
    }

    public function wce_handle_ajax_request(){

        $name=sanitize_text_field($_POST['name']);
        $email=sanitize_text_field($_POST['email']);
        $designation=sanitize_text_field($_POST['designation']);

        $profile_url='';

        if(isset($_FILES['profile_image']['name'])){
            $UploadFile = $_FILES['profile_image'];

            $originalFileName = pathinfo($UploadFile['name'], PATHINFO_FILENAME);

            $file_extension = pathinfo($UploadFile['name'], PATHINFO_EXTENSION);

            $newImageName = $originalFileName."_".time().".".$file_extension;

            $_FILES['profile_image']['name'] = $newImageName;

            $file=wp_handle_upload( $_FILES['profile_image'], array('test_form'=>false));

            $profile_url=$file['url'];
        }

        $this->wpdb->insert($this->table_name,[
            'name'=>$name,
            'email'=>$email,
            'designation'=>$designation,
            'profile_image'=>$profile_url
        ]);

        $employee_id = $this->wpdb->insert_id;

        if ($employee_id > 0) {
             wp_send_json([
                "status" => 1,
                "message" => "Successfully, Employee created"
            ]);
        }else{
             wp_send_json([
                "status" => 0,
                "message" => "Failed to save employee"
            ]);
        }
      
    }

    public function wce_handle_ajax_get_request(){

        $employees = $this->wpdb->get_results(
            "SELECT * FROM {$this->table_name}",
            ARRAY_A
        );

        return wp_send_json([
            "status" => true,
            "message" => "Employees Data",
            "employees" => $employees
        ]);
    }

    public function wce_handle_ajax_delete_request(){

        $employee_id=$_POST['employeeId'];

        $this->wpdb->delete($this->table_name,[
            'id'=>$employee_id
        ]);
        return wp_send_json([
            'status'=>true,
            'message'=>"Employee Deleted Successfully"
        ] );
    }

    public function wce_handle_ajax_get_single_request(){
        $employee_id=$_POST['employeeId'];
        if ($employee_id) {
            
            $employee_data= $this->wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE id = {$employee_id}", ARRAY_A);

            return wp_send_json([
                "status" => true,
                "message" => "employee data success",
                "data"=>$employee_data
            ]);
        }else{
            return wp_send_json([
                "status" => false,
                "message" => "Please pass employee ID"
            ]);
        }
    }

    public function wce_handle_ajax_edit_request(){

        $employee_id=  sanitize_text_field($_POST['employee_id']);

        $employee_name=sanitize_text_field($_POST['employee_name']); 

        $employee_email=sanitize_text_field($_POST['employee_email']); 

        $employee_designation=sanitize_text_field($_POST['employee_designation']);

        $employee_data= $this->getEmployeeData($employee_id);
        
        $profile_url=$employee_data['profile_image'];
       
       

        if (!empty ($_FILES['profile_image']['name'])) {
            // delete the old image
            $wp_site_url = get_site_url();
            $file_path = str_replace($wp_site_url."/", "", $employee_data['profile_image']); 

            if(file_exists(ABSPATH . $file_path)){
                
                unlink(ABSPATH . $file_path);
            }
            // change the file name 
            $UploadFile = $_FILES['profile_image'];

            $originalFileName = pathinfo($UploadFile['name'], PATHINFO_FILENAME);  

            $file_extension = pathinfo($UploadFile['name'], PATHINFO_EXTENSION); 

            $newImageName = $originalFileName."_".time().".".$file_extension;

            $_FILES['profile_image']['name'] = $newImageName;

           $file=wp_handle_upload($_FILES['profile_image'] , array('test_form'=>false) );

           $profile_url=$file['url'];
        }

        if($employee_data){

            $this->wpdb->update($this->table_name, [
                "name" => $employee_name,
                "email" => $employee_email,
                "designation" => $employee_designation,
                "profile_image"=>$profile_url
               
            ], [
                "id" => $employee_id
            ]);
            return wp_send_json( [
                'status'=>true,
                'message'=>'employee updated successfully'
                ] );
        }else{
            return wp_send_json( [
                'status'=>false,
                'message'=>'no employee with this id'
            ] );
        }
       
    }



    private function getEmployeeData($employee_id){

        $employeeData = $this->wpdb->get_row(
            "SELECT * FROM {$this->table_name} WHERE id = {$employee_id}", ARRAY_A
        );

        return $employeeData;
    }
}


?>
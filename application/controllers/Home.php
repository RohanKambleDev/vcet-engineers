<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Home extends CI_Controller
{
    function __construct() {
        parent::__construct();

        // Load facebook library
        $this->load->library('facebook');

        // Load linkedin config
        $this->load->config('linkedin');

        //Load facebook,linkedin user model 
        $this->load->model('users');
    }

    public function index(){
        $data = array();

        if( isset($this->session->userdata['userData']) ){
            $data['userData'] = $this->session->userdata['userData'];
            if( $data['userData']['oauth_provider'] == 'facebook' ){
                $data['logoutUrl'] = $this->facebook->logout_url();
            } else {
                $data['logoutUrl'] = base_url('home/linkedin_logout');
            }
        } else {
            // Get login URL
            $data['authUrl']  =  $this->facebook->login_url();
            $data['oauthURL'] = base_url().$this->config->item('linkedin_redirect_url').'?oauth_init=1';
        }

        // Load header view
        $this->load->view('layout/header');

        // Load login & profile view
        $this->load->view('home/index', $data);

        // Load footer view
        $this->load->view('layout/footer');
    }

    public function facebook_login(){
        $userData = array();

        // Check if user is logged in
        if( $this->facebook->is_authenticated() ){
            // Get user facebook profile details
            $userProfile = $this->facebook->request('get', '/me?fields=id,first_name,last_name,email,gender,locale');
            $profile_picture = $this->facebook->request('get', '/me/picture?redirect=false&type=large');

            if( isset($userProfile['error']) && isset($userProfile['message']) ){
                // error while getting facebook details
                $data['error']     = $userProfile['error'];
                $data['error_msg'] = $userProfile['message'];
            }else{
                // Preparing data for database insertion
                $userData['oauth_provider'] = 'facebook';
                $userData['oauth_uid']      = $userProfile['id'];
                $userData['first_name']     = $userProfile['first_name'];
                $userData['last_name']      = $userProfile['last_name'];
                $userData['email']          = $userProfile['email'];
                $userData['gender']         = $userProfile['gender'];
                $userData['locale']         = $userProfile['locale'];
                $userData['profile_url']    = 'https://www.facebook.com/'.$userProfile['id'];
                $userData['picture_url']    = $profile_picture['data']['url'];

                // Insert or update user data
                $userID = $this->users->checkUser($userData);

                // Check user data insert or update status
                if(!empty($userID)){
                    $data['userData'] = $userData;
                    $this->session->set_userdata('userData',$userData);
                }else{
                   $data['userData'] = array();
                }

                // Get logout URL
                $data['logoutUrl'] = $this->facebook->logout_url();
            }
        }else{
            $fbuser = '';

            // Get login URL
            $data['authUrl'] =  $this->facebook->login_url();
        }

        // Load header view
        $this->load->view('layout/header');

        // Load login & profile view
        $this->load->view('home/index', $data);

        // Load footer view
        $this->load->view('layout/footer');
    }

    public function linkedin_login(){
        $userData = array();
        
        //Include the linkedin api php libraries
        include_once APPPATH."libraries/linkedin-oauth-client/http.php";
        include_once APPPATH."libraries/linkedin-oauth-client/oauth_client.php";        
        
        //Get status and user info from session
        $oauthStatus = $this->session->userdata('oauth_status');
        $sessUserData = $this->session->userdata('userData');
        
        if(isset($oauthStatus) && $oauthStatus == 'verified'){
            //User info from session
            $userData = $sessUserData;
            
            // Get logout URL
            $data['logoutUrl'] = base_url('home/linkedin_logout');
        }elseif((isset($_REQUEST["oauth_init"]) && $_REQUEST["oauth_init"] == 1) || (isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier']))){
            $client = new oauth_client_class;
            $client->client_id = $this->config->item('linkedin_api_key');
            $client->client_secret = $this->config->item('linkedin_api_secret');
            $client->redirect_uri = base_url().$this->config->item('linkedin_redirect_url');
            $client->scope = $this->config->item('linkedin_scope');
            $client->debug = false;
            $client->debug_http = true;
            $application_line = __LINE__;
            
            //If authentication returns success
            if($success = $client->Initialize()){
                if(($success = $client->Process())){
                    if(strlen($client->authorization_error)){
                        $client->error = $client->authorization_error;
                        $success = false;
                    }elseif(strlen($client->access_token)){
                        $success = $client->CallAPI('http://api.linkedin.com/v1/people/~:(id,email-address,first-name,last-name,location,picture-url,public-profile-url,formatted-name)', 
                        'GET',
                        array('format'=>'json'),
                        array('FailOnAccessError'=>true), $userInfo);
                    }
                }
                $success = $client->Finalize($success);
                // Get logout URL
                $data['logoutUrl'] = base_url('home/linkedin_logout');
            }
            
            if($client->exit) exit;
    
            if($success){
                //Preparing data for database insertion
                $first_name = !empty($userInfo->firstName)?$userInfo->firstName:'';
                $last_name = !empty($userInfo->lastName)?$userInfo->lastName:'';
                $userData = array(
                    'oauth_provider' => 'linkedin',
                    'oauth_uid'      => $userInfo->id,
                    'first_name'     => $first_name,
                    'last_name'      => $last_name,
                    'email'          => $userInfo->emailAddress,
                    'locale'         => $userInfo->location->name,
                    'profile_url'    => $userInfo->publicProfileUrl,
                    'picture_url'    => $userInfo->pictureUrl
                );
                
                //Insert or update user data
                $userID = $this->users->checkUser($userData);
                
                //Store status and user profile info into session
                $this->session->set_userdata('oauth_status','verified');
                $this->session->set_userdata('userData',$userData);
                
            }else{
                 $data['error_msg'] = 'Some problem occurred, please try again later!';
            }
        }elseif(isset($_REQUEST["oauth_problem"]) && $_REQUEST["oauth_problem"] <> ""){
            $data['error_msg'] = $_GET["oauth_problem"];
        }else{
            $data['oauthURL'] = base_url().$this->config->item('linkedin_redirect_url').'?oauth_init=1';
        }
        
        $data['userData'] = $userData;
        
        // Load header view
        $this->load->view('layout/header');

        // Load login & profile view
        $this->load->view('home/index', $data);

        // Load footer view
        $this->load->view('layout/footer');
    }

    public function facebook_logout() {
        // Remove local Facebook session
        $this->facebook->destroy_session();

        // Remove user data from session
        $this->session->unset_userdata('userData');

        // Redirect to login page
        redirect('/');
    }

    public function linkedin_logout() {
        //Unset token and user data from session
        $this->session->unset_userdata('oauth_status');
        $this->session->unset_userdata('userData');
        
        //Destroy entire session
        $this->session->sess_destroy();

        // Redirect to login page
        redirect('/');
    }
}
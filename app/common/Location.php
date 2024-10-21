<?php 

## Database configuration
include '../../includes/db-config.php';
session_start();

$url = '';
$options = '<option value="">Select</option>';

$user_location = [];
if (isset($_REQUEST['Id']) && !empty($_REQUEST['Id']) && isset($_REQUEST['table']) && !empty($_REQUEST['table'])) {
    $id = mysqli_real_escape_string($conn,$_REQUEST['Id']);
    $table = mysqli_real_escape_string($conn,$_REQUEST['table']);
    $user_location = $conn->query("SELECT Country,City,State FROM $table  WHERE ID = ".$_REQUEST['Id']);
    $user_location = mysqli_fetch_assoc($user_location);
}


if(!empty($_REQUEST['country']) && !empty($_REQUEST['state'])) {
    $url = 'https://api.countrystatecity.in/v1/countries/'. findCode($_REQUEST['country']) .'/states/'. findCode($_REQUEST['state']) .'/cities'; 
    $lists = getList($url);
    $lists = json_decode($lists,true);
    if (!empty($user_location)) {
        createDropDownList('city',$lists,$user_location['City']);
    } else {
        createDropDownList('city',$lists);
    }
    
} elseif(!empty($_REQUEST['country'])) {
    $url = 'https://api.countrystatecity.in/v1/countries/'. findCode($_REQUEST['country']) .'/states';
    $lists = getList($url);
    $lists = json_decode($lists,true);
    if (!empty($user_location)) {
        createDropDownList('state',$lists,findName($user_location['State']));
    } else {
        createDropDownList('state',$lists);
    }
    
} else {
    $url = 'https://api.countrystatecity.in/v1/countries';
    $lists = getList($url);
    $lists = json_decode($lists,true);
    if (!empty($user_location)) {
        createDropDownList('country',$lists,findName($user_location['Country']));
    } else {
        createDropDownList('country',$lists);
    }
    
}

echo $options;


function getList($url) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
    'X-CSCAPI-KEY: NkhodjdUdEtZNXZlWXdmeks5Q29iN01aZkNQY2NqbkxXMzJTZmtYbA=='
    ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function createDropDownList($list_name,$lists,$selected_val = null) {
    global $options;    
    foreach ($lists as $value) {
        if ($list_name == 'city') {
            if (!is_null($selected_val)) {
                if ($value['name'] == $selected_val) {
                    $options .= '<option value="'.$value['name'].'" selected >'.$value['name'].'</option>';
                } else {
                    $options .= '<option value="'.$value['name'].'">'.$value['name'].'</option>';
                }
            } else {
                $options .= '<option value="'.$value['name'].'">'.$value['name'].'</option>';
            }
                
        } else {
            if(!is_null($selected_val)) {
                if( $value['name'] == $selected_val) {
                    $options .= '<option value="'.$value['name'].'('.$value['iso2'].')" selected >'.$value['name'].'</option>';
                } else {
                    $options .= '<option value="'.$value['name'].'('.$value['iso2'].')">'.$value['name'].'</option>';
                }
            } else {
                $options .= '<option value="'.$value['name'].'('.$value['iso2'].')">'.$value['name'].'</option>';
            }
            
        }
    }
}

function findCode($name) :string {
    preg_match("/\(.*?\)/",$name,$match);
    $code = trim($match[0], '()');
    return $code;
}

function findName($name) : string {
    $data = explode("(",$name);
    return $data[0];
}
?>
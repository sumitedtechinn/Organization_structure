<?php 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

## Database configuration
include '../../includes/db-config.php';

$obj = new TicketServer();
$obj->conn = $conn;
$obj->request = $_REQUEST;

try {
    $obj->getTicketData();
} catch (Exception $e) {
    echo ": got exception => " . $e->getMessage() . " in file => " . $e->getFile() . " on line => " . $e->getLine();
}

class TicketServer {

    public $conn; 
    public $stepsLog; 
    public $request;
    public $finalRes;
    public $draw;
    public $row;
    public $rowperpage;
    public $orderby;
    public $totalRecords;
    public $totalRecordwithFilter;
    public $searchQuery = "";
    public $filterQuery = "";
    public $data = [];

    public function setRequestData() {

        $this->draw = $this->request['draw'];
        $this->row = $this->request['start'];
        $this->rowperpage = $this->request['length'];
        
        if (isset($this->request['order'])) {
            $columnIndex = $this->request['order'][0]['column']; // Column index
            $columnName = $this->request['columns'][$columnIndex]['data']; // Column name 
            $columnSortOrder = $this->request['order'][0]['dir']; // asc or desc
            $this->orderby = "ORDER BY $columnName $columnSortOrder";
        } else {
            $this->orderby = "ORDER BY ticket_record.created_at DESC";
        }

        // $searchValue = mysqli_real_escape_string($this->conn, $this->request['search']['value']); // Search value
        // if (!empty($searchValue)) {
        //     $this->searchQuery .= "AND (ticket_record.task_name LIKE '%$searchValue%')"; 
        // }

        if(isset($this->request['searchText'])) {
            $searchValue = $this->request['searchText'];
            $this->searchQuery .= "AND (ticket_record.task_name LIKE '%$searchValue%')";
        }
        
        if(isset($this->request['erpName'])) {
            $erp_name = strtoupper($this->request['erpName']);
            $this->filterQuery .= "unique_id LIKE '%$erp_name%'";
        }

        if (isset($this->request['loginUser']) && isset($this->request['userRole'])) {
            if ($this->request['userRole'] != 'Administrator') {
                $loginUser_id = $this->request['loginUser'];
                $this->filterQuery .= " AND ticket_record.raised_by = '$loginUser_id'";
            }
        }
    }

    public function getTicketData() {

        $this->setRequestData();

        ## Total number of records without filtering
        $all_count_query = "SELECT COUNT(id) as `allcount` FROM ticket_record WHERE $this->filterQuery";
        $all_count = $this->conn->query($all_count_query);
        $records = mysqli_fetch_assoc($all_count);
        $this->totalRecords = $records['allcount'];

        ## Total number of record with filtering    
        $filter_count_query = "SELECT COUNT(id) as `filtered` FROM ticket_record WHERE $this->filterQuery $this->searchQuery";
        $filter_count = $this->conn->query($filter_count_query);
        $records = mysqli_fetch_assoc($filter_count);
        $this->totalRecordwithFilter = $records['filtered'];

        ## Fetch Record 
        $tickets_query = "SELECT ticket_record.* , DATE_FORMAT(ticket_record.created_at,'%d-%b-%Y') as `create_date` FROM ticket_record WHERE $this->filterQuery $this->searchQuery $this->orderby LIMIT $this->row , $this->rowperpage";
        $tickets = $this->conn->query($tickets_query);
        if ($tickets->num_rows > 0) {
            $i = 1;
            while($row = mysqli_fetch_assoc($tickets)) {
                $statusInfo = $this->conn->query("SELECT name , color FROM `ticket_status` WHERE id = '". $row['status'] ."'");
                $statusInfo = mysqli_fetch_assoc($statusInfo);
                $this->data[] = array(
                    "ID" => $row["id"],
                    "sqNo" => $i,
                    "task_name" => $row['task_name'],
                    "unique_id" => $row['unique_id'],
                    "statusName" => $statusInfo['name'],
                    "statusColor" => $statusInfo['color'],
                    "create_date" => $row['create_date'],
                );
                $i++;
            }
        }

        $this->setResponse();
    }

    public function setResponse() {

        $this->finalRes = array(
            "draw" => intval($this->draw),
            "iTotalRecords" => $this->totalRecords,
            "iTotalDisplayRecords" => $this->totalRecordwithFilter,
            "aaData" => $this->data
        );

        echo json_encode($this->finalRes);
    }
}
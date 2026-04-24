<?php
// echo "Testing";
function getUpcomingEvents($sql, $params)
{
	$sort = 'r.startDate';
	
	if(array_key_exists('sort', $params))
	{
		switch($params['sort'])
		{
			case 'id':
				$sort = 'r.id';
				break;
			case 'location':
				$sort = 'l.name';
				break;
			case 'name':
				$sort = 'p.lastName';
				break;
			case 'startDate':
				$sort = 'r.startDate';
				break;
			case 'type':
				$sort = 't.typeName';
				break;
			case 'attendance':
				$sort = 'b.expectedNumOfGuests';
				break;
		}
	}
	
	$search = 
		array_key_exists('search', $params)
		? "%{$params['search']}%"
		: '%%';
	
	$type =
        array_key_exists('type', $params)
            ? $params['type']
            : "";

    $format = 'Y-m-d';

    $from =
        array_key_exists('from', $params)
            ? date($format, strtotime($params['from']))
            : date($format);
	
	if(!$from) {
        $from = date($format);
    }

    $to =
        array_key_exists('to', $params)
            ? date($format, strtotime($params['to']))
            : date($format);

    if(!$to) {
        $to = date($format);
    }
	$order = 
        array_key_exists('order', $params)
            ? $params['order'] === 'asc' 
                ? "ASC" 
                : "DESC"
            : "DESC";

    $limit = 
        array_key_exists('limit', $params)
            ? intval($params['limit'])
            : 50;

    if($limit === -1) {
        $limit = PHP_INT_MAX;
    }

    $page = 
        array_key_exists('page', $params)
            ? intval($params['page'])
            : 1;

    $offset = $limit * ($page - 1);

    $query_str = "
       SELECT 
	   	SQL_CALC_FOUND_ROWS
		*
	   FROM members
	   LEFT JOIN people
	   ON members.peopleid = people.id
	   WHERE
            (
                r.id LIKE :search
                OR l.name LIKE :search
                OR p.lastName LIKE :search
                OR DATE_FORMAT(r.startDate, '%a %b %e, %l:%i %p') LIKE :search
                OR DATE_FORMAT(r.endDate, '%l:%i %p') LIKE :search
                OR t.typeName LIKE :search
                OR b.expectedNumOfGuests LIKE :search
            )
	   AND status != 86 
	   ORDER BY members.id DESC
    ";
	echo $query_str;
    $stmt = $sql->prepare($query_str);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    if(strlen($type) !== 0) {
        $stmt->bindParam(':type', $type);
    }
    $stmt->bindParam(':from', $from, PDO::PARAM_STR);
    $stmt->bindParam(':to', $to, PDO::PARAM_STR);
    $stmt->bindParam(':search', $search);
    $stmt->execute();

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pages = ceil($sql->query("
        SELECT FOUND_ROWS();
    ")->fetch()[0] / $limit);

    echo json_encode(array(
        'page' => $page,
        'pages' => $pages,
        'results' => $events,
		'sql' => $query_str
    ));
}

?>
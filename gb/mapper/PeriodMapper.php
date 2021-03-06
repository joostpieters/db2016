<?php
namespace gb\mapper;

$EG_DISABLE_INCLUDES=true;
require_once( "gb/mapper/Mapper.php" );
require_once( "gb/domain/Writer.php" );



class PeriodMapper extends Mapper {

    function __construct() {
        parent::__construct();
        $this->selectStmt = "SELECT a.*, b.* from person a, writer b where a.uri = b.writer_uri and a.uri = ?";
        $this->selectAllStmt = "SELECT a.*, b.* from person a, writer b where a.uri = b.writer_uri";        
    } 
    
    function getCollection( array $raw ) {
        
        $customerCollection = array();
        foreach($raw as $row) {
            array_push($customerCollection, $this->doCreateObject($row));
        }
        
        return $customerCollection;
    }

    protected function doCreateObject( array $array ) {
        
        $obj = null;        
        if (count($array) > 0) {
            $obj = new \gb\domain\Writer( $array['uri'] );

            $obj->setUri($array['uri']);
            $obj->setFullName($array['full_name']);
            $obj->setDescription($array['description']);
            $obj->setDateOfBirth($array['birth_date']);
            $obj->setDateofDeath($array['death_date']);
			$obj->setNumberOfBooks($array['number_of_books']);
        }
        
        return $obj;
    }

    protected function doInsert( \gb\domain\DomainObject $object ) {
        /*$values = array( $object->getName() ); 
        $this->insertStmt->execute( $values );
        $id = self::$PDO->lastInsertId();
        $object->setId( $id );*/
    }
    
    function update( \gb\domain\DomainObject $object ) {
        $values = array( $object->getName(), $object->getId(), $object->getId() ); 
        $this->updateStmt->execute( $values );
    }

    function selectStmt() {
        return $this->selectStmt;
    }
    
    function selectAllStmt() {
        return $this->selectAllStmt;
    }
    
	//return an array of active writers within the given time period including the number of books written
	function getActiveWriters ($start_period, $end_period) {
        $con = $this->getConnectionManager();
        $selectStmt = "	SELECT		p.*, w.*, b.first_publication_date, COUNT(w.book_uri) AS 'number_of_books'
						FROM		person p, writes w, book b
						WHERE		p.uri = w.writer_uri
									AND w.book_uri = b.uri
									AND b.first_publication_date > " ."\"" . $start_period . "\""."
									AND b.first_publication_date < " ."\"" . $end_period . "\""."
						GROUP BY	p.uri
						ORDER BY	number_of_books DESC";
        $writers = $con->executeSelectStatement($selectStmt, array()); 
        #print $selectStmt;
        return $this->getCollection($writers);
    }
	
}


?>
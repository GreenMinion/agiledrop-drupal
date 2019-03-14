<?php

namespace Drupal\countdown;

/**
 * Defines Countdownservice class.
 */
class Countdownservice {

    /**
     * Constructs a connection object.
     *
     */
    public function __construct()
    {
        // Instantiate a connection object
        $this->dataBase = \Drupal::database();
    }

    /*
     * Query the database for specific event date
     *
     * @param (int) $nid
     *
     * @return
     *    Date string
     */
    public function fetchEventDate($nid){
        $returnDate = 0;

        // The transaction opens here.
        $txn = $this->dataBase->startTransaction();
        try {
            $query = $this->dataBase->select('node__field_event_date','eDate')
            ->condition('eDate.entity_id', $nid, '=')
            ->condition('eDate.deleted', 0, '=')
            ->fields('eDate', ['field_event_date_value'])
            ->execute();

            $eDate = $query->fetchAssoc();

            if(!empty($eDate)){
                $returnDate = $eDate;
            }
        }
        catch (Exception $e) {
            // Something went wrong somewhere, so roll back now.
            $txn->rollBack();
            // Log the exception to watchdog.
            \Drupal::logger('type')->error($e->getMessage());
        }

        return $returnDate;

    }

    /*
     * Compares two dates and returns the string to be shown in CountDown Block
     *
     * @param (string) $date
     * @param (string) $eventDate
     *
     * @return
     *    String explaining if event allredy started, is happening or is due date
     *
     * Implements returnDiffDateString($interval).
     */
    public function compareDateStrings($date, $eventDate){
        // Provide the same date format for both variables
        $date = new \DateTime(date('Y-m-d',strtotime($date)));
        $eventDate = new \DateTime(date('Y-m-d',strtotime($eventDate['field_event_date_value'])));
        $interval = (int)date_diff($date, $eventDate)->format('%R%a');

        $returnString = $this->returnDiffDateString($interval);

        return $returnString;
    }

    /*
     * Sets a string depending on negative or positive parameter value
     *
     * @param (int) $interval
     *
     * @return
     *    String explaining if event allredy started, is happening or is due date
     */
    public function returnDiffDateString($interval){
        switch($interval){
            case 0:
                $returnString = 'This event is happening today';
                break;
            case ($interval < 0):
                $returnString = 'This event already passed.';
                break;
            case ($interval > 0):
                $days = $interval == 1 ? ' day' : ' days';
                $returnString = $interval .  $days . ' left until event starts.';
                break;
            default:
                $returnString = '';
        }

        return $returnString;
    }
}
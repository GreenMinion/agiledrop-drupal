<?php

namespace Drupal\countdown;

/**
 * Defines Countdownservice class.
 */
class Countdownservice {

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
        $eventDate = new \DateTime(date('Y-m-d',strtotime($eventDate)));
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
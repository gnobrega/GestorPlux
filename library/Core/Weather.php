<?php
class Weather {

        /**
         * @var string
         */
        private $url = "http://api.wunderground.com/api/25ef4a6a7893cd5b/%s/q/%s.json";
        /**
         * @var string
         */
        private $query;

        /**
         * @var string
         */
        private $currentTemperature;

        /**
         * @var string
         */
        private $currentHumidity;

        /**
         * @var array
         */
        private $forecast=array();

        /**
         * @var int
         */
        private $time;

        /**
         * @var string
         */
        private $icon = "";

        /**
         * @param string $query
         */
        public function Weather($query) {
                $this->query = $query;
                $this->loadWeather();
        }

        /**
         * @return ForecastDay

         */
        public function getForecast() {
                return $this->forecast;
        }

        private function loadWeather() {
                //echo "buscando conditions...\n";
                $conditions = json_decode(file_get_contents(sprintf($this->url, "conditions", $this->query)));
                //echo "buscando forecast...\n";
                $forecast   = json_decode(file_get_contents(sprintf($this->url, "forecast", $this->query)));

                $this->currentTemperature = (int)$conditions->current_observation->temp_c;
                $this->currentHumidity = (int)$conditions->current_observation->relative_humidity;
                $this->icon = $conditions->current_observation->icon;
                #$this->time = $coditions->current_observation->observation_time_rfc822;

                $forecastDay = $forecast->forecast->simpleforecast->forecastday;

                $current = true;

                foreach($forecastDay as $day) {
                        $fd = new ForecastDay();
                        $fd->setMax($day->high->celsius);
                        $fd->setMin($day->low->celsius);

                        $fday = new ForecastPart();

                        if ($current) {
                                $fday->setIcon($conditions->current_observation->icon);
                        } else {
                                $fday->setIcon($day->icon);
                        }

                        $fday->setRainProspect($day->pop);

                        $fd->setDay($fday);

//                      $fnight = new ForecastPart();

                        $this->forecast[] = $fd;

                        $current = false;
                }
        }


        public function getIcon()
        {
            return $this->icon;
        }

        public function getTime() {
                return $this->time;
        }

        /**
         * @return string
         */
        public function getCurrentHumidity() {
                return $this->currentHumidity;
        }

        /**
         * @return string
         */
        public function getCurrentTemperature() {
                return $this->currentTemperature;
        }

}


class ForecastDay {

        /**
         * @var integer
         */
        private $min;
        /**
         * @var integer
         */
        private $max;
        /**
         * @var ForecastPart
         */
        private $day;
        /**
         * @var ForecastPart
         */
        private $night;



        /**
         * @return integer
         */
        public function getMin(){
                return $this->min;
        }

        /**
         * @param integer min
         */
        public function setMin($value){
                $this->min = $value;
        }

        /**
         * @return integer
         */
        public function getMax(){
                return $this->max;
        }

        /**
         * @param integer max
         */
        public function setMax($value){
                $this->max = $value;
        }

        /**
         * @return ForecastPart
         */
        public function getDay(){
                return $this->day;
        }

        /**
         * @param ForecastPart day
         */
        public function setDay(ForecastPart &$value){
 
               $this->day = $value;
        }

        /**
         * @return ForecastPart
         */
        public function getNight(){
                return $this->night;
        }

        /**
         * @param ForecastPart night
         */
        public function setNight(ForecastPart &$value){
                $this->night = $value;
        }



}

class ForecastPart {
        /**
         * @var string
         */
        private $icon;
        /**
         * @var integer
         */
        private $rainProspect;

        /**
         * @return string
         */
        public function getIcon(){
                return $this->icon;
        }

        /**
         * @param string icon
         */
        public function setIcon($value){
                $this->icon = $value;
        }

        /**
         * @return integer
         */
        public function getRainProspect(){
                return $this->rainProspect;
        }

        /**
         * @param integer rainProspect
         */
        public function setRainProspect($value){
                $this->rainProspect = $value;
        }


}

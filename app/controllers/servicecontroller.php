<?php

class ServiceController extends Action
{	
	public function index()
	{
            return array();
	}

        public function twitterUserTimeLine()
        {
            $twit = new Twitter();

            $user = $this->params[0];
            $count = (int) $this->params[1];

            return $twit->getUserTimeLine($user, $count);
        }

}

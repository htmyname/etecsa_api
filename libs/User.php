<?php
/*
 * Copyright (c) 2020. [D_n]Codex
 */

class User
{
    private $user;
    private $password;

    /**
     * User constructor.
     * @param $user
     * @param $password
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

}
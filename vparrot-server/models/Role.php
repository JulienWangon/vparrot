<?php

class Role {

    private ?int $idRole = null;
    private ?string $roleName;

    public function __construct(?string $roleName = null, ?int $idRole = null) {

        $this->idRole = $idRole;
        $this->roleName = $roleName;
    }

//Getter List 
    public function getIdRole() : ?int {
        return $this->idRole;
    }

    public function getRoleName() :string {
        return $this->roleName;
    }

//Setter List 
    public function setIdRole($idRole) :void {
        $this->idRole = $idRole;
    }

    public function setRoleName($roleName) :void {
        $this->roleName = $roleName;
    }


    
}
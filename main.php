<?php

class DNConnect
{
    public function getPDO()
    {
        try {
            // On se connecte à MySQL
            $mysqlClient = new PDO('mysql:host=localhost;dbname=carnet;charset=utf8', 'root');
        } catch (Exception $e) {
            // En cas d'erreur, on affiche un message et on arrête tout
            die('Erreur : ' . $e->getMessage());
        }
        return $mysqlClient;
    }
}

class ContactManager
{
    public function findAll()
    {
        $Db = new DNConnect;
        $Data = $Db->getPDO();
        $sqlQuery = 'SELECT * FROM contact';
        $conyactsStatement = $Data->prepare($sqlQuery);
        $conyactsStatement->execute();
        $allContacts = $conyactsStatement->fetchAll(PDO::FETCH_CLASS, Contact::class); // tableau d'objet
        return $allContacts;
    }
    public function findById(int $id)
    {
        $Db = new DNConnect;
        $Data = $Db->getPDO();
        $sqlQuery = 'SELECT * FROM contact where id=?';

        $conyactsStatement = $Data->prepare($sqlQuery);
        $conyactsStatement->execute([$id]);
        $contactItem = $conyactsStatement->fetchObject(Contact::class); // objet

        //autre methode
        // $contactItem = $conyactsStatement->fetch();
        // $contact = new Contact;
        // $contact->setId($contactItem["id"]);
        // $contact->setName($contactItem["name"]);
        // $contact->setEmail($contactItem["email"]);
        // $contact->setPhone_number($contactItem["phone_number"]);
        return $contactItem;
    }
    public function createContact($name, $email, $phone_number)
    {
        $Db = new DNConnect;
        $Data = $Db->getPDO();
        $sqlQuery = "INSERT INTO contact (name, email, phone_number) VALUES (:name, :email, :phone_number)";
        $contactsStatement = $Data->prepare($sqlQuery);
        $contactsStatement->bindParam(':name', $name);
        $contactsStatement->bindParam(':email', $email);
        $contactsStatement->bindParam(':phone_number', $phone_number);
        $contactsStatement->execute();
        $contactItem = $contactsStatement->fetch();
        $command = new Command;
        $command->detail($Data->lastInsertId());
    }

    public function deleteContact($id)
    {
        $Db = new DNConnect;
        $Data = $Db->getPDO();
        $sqlQuery = 'DELETE FROM `contact` WHERE id=?';
        $conyactsStatement = $Data->prepare($sqlQuery);
        $conyactsStatement->execute([$id]);
        $contactItem = $conyactsStatement->fetchObject(Contact::class); // objet
    }

    public function updateContact($id, $name, $email, $phone_number)
    {
        $Db = new DNConnect;
        $Data = $Db->getPDO();
        $sqlQuery = "UPDATE `contact` SET `name` = :name, `email` = :email, `phone_number` = :phone_number WHERE `id` = :id";
        $contactsStatement = $Data->prepare($sqlQuery);
        $contactsStatement->bindParam(':name', $name);
        $contactsStatement->bindParam(':email', $email);
        $contactsStatement->bindParam(':phone_number', $phone_number);
        $contactsStatement->bindParam(':id', $id);
        $contactsStatement->execute();
        $contactItem = $contactsStatement->fetch();
        $command = new Command;
    }
}

class Contact
{
    private $id;
    private $name;
    private $email;
    private $phone_number;


    public function getId()
    {
        $this->id;
    }
    public function setId(int $id)
    {
        $this->id = $id;
    }
    public function getName()
    {
        $this->name;
    }
    public function setName(string $name)
    {
        $this->name = $name;
    }
    public function getEmail()
    {
        $this->email;
    }
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getPhone_number()
    {
        $this->phone_number;
    }
    public function setPhone_number(string $phone_number)
    {
        $this->phone_number = $phone_number;
    }
    public function  __toString()
    {
        return $this->id . " , " . $this->name . " , " . $this->email . " , " . $this->phone_number . "\n";
    }
}

class Command
{
    public function list()
    {
        $contactManager = new ContactManager;
        $list = $contactManager->findAll();
        foreach ($list as $key) {;
            echo $key;
        }
    }
    public function detail($id)
    {
        $oneContact = new ContactManager;
        $contact = $oneContact->findById($id);
        echo $contact;
    }
    public function create($name, $email, $phone_number)
    {
        $oneContact = new ContactManager;
        $contact = $oneContact->createContact($name, $email, $phone_number);
    }

    public function delete($id)
    {
        $oneContact = new ContactManager;
        $contact = $oneContact->deleteContact($id);
        $command = new Command;
        $command->list();
    }
    public function update($id, $name, $email, $phone_number)
    {
        $oneContact = new ContactManager;
        $contact = $oneContact->updateContact($id, $name, $email, $phone_number);
        $command = new Command;
        $command->list();
    }
}


while (true) {
    $line = readline("Entrez votre commande : ");
    echo "Vous avez saisi : $line\n";
    $detailPattern = '/detail\s+(\d+)/';
    $deletePattern = '/delete\s+(\d+)/';
    $updatePattern = '/update\s+(\d+)/';

    if ($line === "list") {
        echo "Affichage de la liste \n";
        $command = new Command;
        $command->list();
    }
    if (preg_match($detailPattern, $line, $matches)) {
        $command = new Command;
        $command->detail($matches[1]);
    }
    if ($line === "create") {
        $name = readline("Nom:");
        $email = readline("email:");
        $phone_number = readline("numéro de téléphone:");
        $command = new Command;
        $command->create($name, $email, $phone_number);
    }
    if (preg_match($deletePattern, $line, $matches)) {
        $command = new Command;
        $command->delete($matches[1]);
    }
    if (preg_match($updatePattern, $line, $matches)) {
        $name = readline("Nom:");
        $email = readline("Email:");
        $phone_number = readline("Numéro de téléphone:");
        $command = new Command;
        $command->update($matches[1], $name, $email, $phone_number);
    }
}

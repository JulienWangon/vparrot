<?php

require_once './vparrot-server/models/Database.php';
require_once './vparrot-server/models/Testimonies.php';

class TestimoniesRepository extends Database {

    /**
     * Récupère tous les témoignages stockés dans la base de données.
     *
     * Cette méthode interroge la base de données pour extraire tous les témoignages existants.
     * Elle renvoie une liste d'objets 'Testimonies', chacun représentant un témoignage.
     * Chaque témoignage inclut des informations telles que l'identifiant, le prénom, le nom,
     * le contenu du témoignage, la note attribuée, et le statut de modération.
     *
     * @return array Renvoie un tableau d'objets 'Testimonies', chacun contenant les informations d'un témoignage.
     *               Renvoie un tableau vide si aucun témoignage n'est trouvé.
     * @throws PDOException Si une erreur survient lors de la requête à la base de données.
    */
   
    public function getAllTestimonies() :array {

      try {

          $db = $this->getBdd();
          $req = "SELECT * FROM testimonies";

          $stmt = $db->prepare($req);
          $stmt->execute();
          $testimoniesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

          $testimonies = [];
          foreach ($testimoniesData as $testimonyData) {

              $testimony = new Testimonies(  
                  $testimonyData['first_name'],
                  $testimonyData['last_name'],
                  $testimonyData['content'],
                  $testimonyData['rating'],
                  $testimonyData['status'],
                  $testimonyData['id_testimony'] ?? null
              );

              $testimonies[] = $testimony;     
          }

          return $testimonies;

      } catch(PDOException $e) {

          $this->handleException($e, "extraction de tous les avis clients");
      }
    }





    //Create testimony
    public function addTestimony(Testimonies $testimony) :string {
        try {

            $db = $this->getBdd();
            $req = "INSERT INTO testimonies (first_name, last_name, content, rating, status) VALUES (:firstName, :lastName, :content, :rating, 'en attente')";
            
            $stmt = $db->prepare($req);
            $stmt->bindValue(":firstName", $testimony->getFirstName() , PDO::PARAM_STR);
            $stmt->bindValue(":lastName", $testimony->getLastName(), PDO::PARAM_STR);
            $stmt->bindValue(":content", $testimony->getContent() , PDO::PARAM_STR);
            $stmt->bindValue(":rating", $testimony->getRating() , PDO::PARAM_INT);
            $stmt->execute();

            $lastInsertId = $db->lastInsertId();
            return $lastInsertId;

        } catch(PDOException $e) {

            $this->handleException($e, "Création du témoignage. ");                     
        }
    }


    /**
     * Approuve un témoignage en mettant à jour son statut de modération dans la base de données.
     *
     * Cette méthode met à jour le statut de modération d'un témoignage spécifique, marquant ainsi
     * le témoignage comme étant approuvé. Elle prend en paramètre un objet 'Testimonies' et utilise
     * son identifiant pour identifier le témoignage à approuver dans la base de données.
     *
     * @param Testimonies $testimony L'objet témoignage à approuver.
     * @return bool Renvoie vrai (true) si l'opération de mise à jour a affecté au moins une ligne,
     *              ce qui signifie que le témoignage a été approuvé. Renvoie faux (false) si aucune
     *              ligne n'a été affectée.
     * @throws PDOException Si une erreur survient lors de la requête à la base de données.
     */


    //Approve testimony
    public function approveTestimony(Testimonies $testimony) : bool {
    try {
        $db = $this->getBdd();
        // Mise à jour de la requête pour changer le statut
        $req = "UPDATE testimonies SET status = 'approuvé' WHERE id_testimony = :id";

        $stmt = $db->prepare($req);
        $stmt->bindValue(":id", $testimony->getIdTestimony(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;

    } catch(PDOException $e) {
        $this->handleException($e, "approbation du témoignage");
    }
}

    /**
     * Rejette un témoignage en le transférant dans une table spécifique des témoignages rejetés.
     *
     * Cette méthode effectue une opération en deux étapes : elle copie d'abord le témoignage spécifié
     * depuis la table principale des témoignages vers une table dédiée aux témoignages rejetés, puis
     * supprime le témoignage original de la table principale. L'opération est effectuée en tant que transaction
     * pour s'assurer que les deux étapes réussissent ou échouent ensemble. Si une erreur survient pendant
     * la transaction, celle-ci est annulée (rollback) pour maintenir la cohérence des données.
     *
     * @param Testimonies $testimony L'objet témoignage à rejeter.
     * @return bool Renvoie vrai (true) si le témoignage est correctement copié dans la table des témoignages rejetés.
     *              Renvoie faux (false) si l'opération échoue ou si aucune ligne n'est affectée.
     * @throws PDOException Si une erreur survient lors de la requête à la base de données ou durant la transaction.
     */

    //Rejecte Testimony
    public function rejectTestimony(Testimonies $testimony) : bool {
        try {
            $db = $this->getBdd();
    
            // Mise à jour du statut du témoignage à 'rejeté'
            $req = "UPDATE testimonies SET status = 'rejeté' WHERE id_testimony = :id";
    
            $stmt = $db->prepare($req);
            $stmt->bindValue(":id", $testimony->getIdTestimony(), PDO::PARAM_INT);
            $stmt->execute();
    
            return $stmt->rowCount() > 0;
    
        } catch(PDOException $e) {
            $this->handleException($e, "rejet du témoignage");
        }
    }


    /**
     * Supprime un témoignage spécifique de la base de données.
     *
     * Cette méthode supprime un témoignage de la table des témoignages en utilisant l'identifiant du témoignage.
     * Le témoignage à supprimer est identifié par l'objet 'Testimonies' passé en paramètre. La méthode utilise
     * l'identifiant du témoignage pour cibler et supprimer l'enregistrement correspondant dans la base de données.
     *
     * @param Testimonies $testimony L'objet témoignage à supprimer, contenant l'identifiant du témoignage.
     * @return bool Renvoie vrai (true) si la suppression a réussi et a affecté au moins une ligne dans la table.
     *              Renvoie faux (false) si aucune ligne n'a été affectée, indiquant qu'aucun témoignage avec
     *              l'identifiant spécifié n'a été trouvé.
     * @throws PDOException Si une erreur survient lors de l'exécution de la requête à la base de données.
    */
   
    //Delete testimony 
    public function deleteTestimony(Testimonies $testimony) : bool {

        try {

            $db = $this->getBdd();
            $req = "DELETE FROM testimonies WHERE id_testimony = :id";
            
            $stmt = $db->prepare($req);
            $stmt->bindValue(":id", $testimony->getIdTestimony(), PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch(PDOException $e) {

            $this->handleException($e, "suppression du témoignage");
        }
    }


    //Checking if testimony id exists
    public function testimonyExists(int $testimonyId) :bool {

        try {

            $db = $this->getBdd();
            $req = "SELECT 1 FROM testimonies WHERE id_testimony = :id";
            $stmt = $db->prepare($req);
            $stmt->bindValue(":id", $testimonyId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() !== false;

        } catch(PDOException $e) {

            $this->handleException($e, "vérification de l'existance du témoignage");
        }
    }


    /**
     * Vérifie si un témoignage existe dans la base de données en fonction de son identifiant.
     *
     * Cette méthode interroge la table des témoignages pour déterminer si un enregistrement avec l'identifiant
     * spécifié existe. Elle renvoie un booléen indiquant le résultat de cette vérification.
     *
     * @param int $testimonyId L'identifiant du témoignage à vérifier.
     * @return bool Renvoie vrai (true) si un témoignage avec l'identifiant spécifié existe dans la base de données.
     *              Renvoie faux (false) si aucun témoignage correspondant n'est trouvé.
     * @throws PDOException Si une erreur survient lors de l'exécution de la requête à la base de données.
    */

    public function findTestimonyById($idTestimony) {
        try {
            $db = $this->getBdd();
            $req = "SELECT * FROM testimonies WHERE id_testimony = :id";
    
            $stmt = $db->prepare($req);
            $stmt->bindValue(":id", $idTestimony, PDO::PARAM_INT);
            $stmt->execute();
    
            $testimonyData = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($testimonyData) {
                $testimony = new Testimonies();
                $testimony->setIdTestimony($testimonyData['id_testimony']);
                $testimony->setFirstName($testimonyData['first_name']);
                $testimony->setLastName($testimonyData['last_name']);
                $testimony->setContent($testimonyData['content']);
                $testimony->setRating($testimonyData['rating']);
                $testimony->setStatus($testimonyData['status']); // Assurez-vous que c'est 'status', pas 'is_moderated'
    
                return $testimony;
            } else {
                // Gérer le cas où aucun témoignage n'est trouvé
                return null; // ou toute autre valeur ou action indiquant qu'aucun témoignage n'a été trouvé
            }
    
        } catch(PDOException $e) {
            $this->handleException($e, "Recherche d'un témoignage par ID");
            // Retourner false ou null selon la convention de votre application en cas d'erreur
            return false;
        }
    }






}

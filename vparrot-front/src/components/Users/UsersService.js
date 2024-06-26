import instanceAxios from "../../_utils/axios";


//Optenir la liste des utilisateurs et leur rôle
export const fetchAllUsers = async () => {
    try {

        const response = await instanceAxios.get('/users');
        if (response.data && response.data.status === 'success') {
          return response.data.data
         
      } else {

          throw new Error(response.data.message || "Données reçues non valides ou erreur de requête.");
      }
    } catch (error) {

      const errorMessage = error.response?.data?.message ?? "Erreur lors de la communication avec l'API.";
      console.error('Erreur lors de la récupération des témoignages:', errorMessage);

      throw new Error(errorMessage);
    }
};


//Ajouter un nouvel utilisateur 
export const addUser = async (userData, csrfToken) => {
    try {

        const requestBody = {
            ...userData,
            csrfToken: csrfToken
        };


        const response = await instanceAxios.post('/users', requestBody);
     
        if (response.data && response.data.status === 'success') {
            return response.data;
        } else {
       
            throw new Error(response.data.message|| "Erreur lors de la communication avec l'API.");
        }
        
    } catch (error) {

        const errorMessage = error.response?.data?.message ?? "Erreur lors de la communication avec l'API.";
        console.error('Erreur lors de l\'ajout de l\'utilisateur:', errorMessage);
        throw new Error(errorMessage);
    }
};


//Mise à jour d'un utilisateur 
export const updateUser = async (idUser, formData, csrfToken) => {

    try {

        const response = await instanceAxios.put(`/user/${idUser}/update`, formData, {
            headers:  {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        if(response.data && response.data.status === "success") {

            return response.data;
        } else {

            throw new Error(response.data.message || "Erreur lors de la communication avec l'API.");
        }


    } catch (error) {

        const errorMessage = error.response?.data?.message ?? "Erreur lors de la communication avec l'API.";
        console.error('Erreur lors de la mise à jour de l\'utilisateur:', errorMessage);
        throw new Error(errorMessage);
    }
};

//Supprimer un utilisateur
export const deleteUser = async (idUser, csrfToken) => {

    try {

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken 
        };

        const response = await instanceAxios.delete(`/users/${idUser}/delete`, { headers });

        if(response.data && response.data.status === 'success') {

            return response.data;
        } else {

            throw new Error(response.data.message || "Erreur lors de la communication avec l'API.");
        }

    } catch (error) {

        const errorMessage = error.response?.data?.message ?? "Erreur lors de la communication avec l'API.";
        console.error('Erreur lors de la suppression l\'utilisateur:', errorMessage);
        throw new Error(errorMessage);
    }


}




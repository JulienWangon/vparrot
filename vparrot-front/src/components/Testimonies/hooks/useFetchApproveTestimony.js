import { useState } from 'react';

//Import fonction du service pour approuver un avis client
import { approveTestimony } from '../testimoniesService';

//import du context de message pour afficher des notification
import { useMessage } from '../../../contexts/MessagesContext';

//import du context d'authentification pour récupérer des données utilisateur
import { useAuth} from '../../../contexts/AuthContext';

const useFetchApproveTestimony = () => {

    //Utilisation du context pour afficher des notifications
    const { showMessage } = useMessage();

    //Etat pour gérer l'indicateur de chargement
    const [isLoading, setIsLoading] = useState(false);

    ///Récupération du token csrf depuis le context d'authentification
    const { csrfToken } = useAuth();

    //Focntion asynchrone pour approuver un avis client
    const approveThisTestimony = async (idTestimony) => {

        //Activation de l'indiczteur de chargement
        setIsLoading(true);

        try {
            //Appelle a la fonction du service pour approuver l avis client
            const response = await approveTestimony(idTestimony, csrfToken);
            //Affichage du message de succès via le context message
            showMessage({ data: response }, 'success');
            return response;
        } catch (error) {
            //Affichage du message d'erreur et propagation de l'erreur
            showMessage({ data: error.response }, 'error');
            throw error;

        } finally {
            //Désactivation de l'indicateur de chargement
            setIsLoading(false);
        }
    };

    //Retourne la fonction d'approbation et létat de chargmeent pour une utilisation ultèrieur
    return { approveThisTestimony, isLoading };
}

export default useFetchApproveTestimony;
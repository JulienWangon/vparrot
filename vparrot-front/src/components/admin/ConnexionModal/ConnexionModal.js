import React, { useState } from 'react';
import { useAuth } from '../../../contexts/AuthContext';
import { Link } from 'react-router-dom';

import ReCAPTCHA from "react-google-recaptcha";

import H2Title from '../../common/H2Title/H2Title';
import TextInput from '../../common/Input/TextInput/TextInput';
import Button from '../../common/Buttons/Button/Button';

import { validateEmail, validatePassword } from '../../../_utils/validation';

import ModalStyle from './connexionModal.module.css';


const reCaptchaKey ="6Le8ugwpAAAAAGo_7BMdYwZ_gZfNGLLXcCqb_TXC";

const ConnexionModal = ({ handleCloseModal }) => {

    const { login, error, loading, clearErrors } = useAuth();
    const [captchaValue, setCaptchaValue] = useState(false);
    const [data, setData] = useState({ email: "", password: "" });
    const [errors, setErrors] = useState({});
    const [areFieldsValid, setAreFieldsValid] = useState(false);

    const validate = () => {
        let isValid = true;
        let newErrors = {};
        // Utiliser les fonctions importées pour la validation
        newErrors.email = validateEmail(data.email);
        newErrors.password = validatePassword(data.password);
        // Vérifie si des erreurs ont été définies
        if(newErrors.email || newErrors.password) {
            isValid = false;
        }
        // Mettre à jour l'état des erreurs dans le composant        
        setErrors(newErrors);
        setAreFieldsValid(isValid);
        // Retourner si le formulaire est valide ou non         
        return isValid;
    };


    const handleChange = (e) => {
        const { name, value } = e.target;
        setData((prevData) => ({ ...prevData, [name]: value }));
        setErrors({}); 

        let newErrors = {
            email: name === 'email' ? validateEmail(value) : validateEmail(data.email),
            password: name === 'password' ? validatePassword(value) : validatePassword(data.password)
        };
        setErrors(newErrors);

        const isValid = !newErrors.email && !newErrors.password;
        setAreFieldsValid(isValid);

        clearErrors();
 
    };

    
    const handleCaptcha = (value) => {
        setCaptchaValue(value); // Mettre à jour la valeur du CAPTCHA
    };


    const handleSubmit = async (e) => {
    e.preventDefault();

        if (validate()) {

            const response = await login(data.email, data.password, captchaValue);

            if (response && response.data && response.data.status === 'success') {
                // Connexion réussie
                setCaptchaValue(null);
                handleCloseModal();    
            }     
        } 
    };

    return (


        <div className={ModalStyle.modalOverlay}>
                <div className={ModalStyle.formContainer}>
                
                    <Button className={ModalStyle.circleBtn} colorStyle="whiteBtn" onClick={handleCloseModal}>X</Button>

                    <form method="POST" onSubmit={handleSubmit} className={ModalStyle.loginForm}>
                        <div className={ModalStyle.titleContainer}>
                        <H2Title className={ModalStyle.useFormTitle} h2Text="Identifiez vous"/>
                        </div>
                        <TextInput
                            inputClassName={ModalStyle.loginInput}
                            formGroupClass={ModalStyle.formGroup}
                            label="Email"
                            name="email"
                            value={data.email}
                            onChange={handleChange}
                            placeholder="Entrez votre email"
                        />
                        {errors.email && <div className="errorMessage">{errors.email}</div>}

                        <TextInput
                            inputClassName={ModalStyle.loginInput}
                            formGroupClass={ModalStyle.formGroup}
                            label="Mot de passe"
                            name="password"
                            value={data.password}
                            onChange={handleChange}
                            placeholder="Entrez votre mot de passe"                            
                        />
                        {errors.password && <div className="errorMessage">{errors.password}</div>}
                        <Link to="/reset-password-request" className={ModalStyle.passwordResetLink}>Mot de passe oublié</Link>

                        {areFieldsValid && (
                            <ReCAPTCHA
                                sitekey={reCaptchaKey}
                                onChange={handleCaptcha}
                            />
                        )}
                        
                       
                        <Button type="submit"className={ModalStyle.loginBtn} colorStyle="redBtn" disabled={!captchaValue && areFieldsValid}>Connexion</Button>
                       
                        {error && <div className="errorMessage">{error}</div>}
                        {loading && <div className="loadingMessage">Chargement...</div>}

                    </form>      
                    
                </div>
        </div>
    );
};

export default ConnexionModal;
import React, { useState, useEffect } from 'react';
import ReCAPTCHA from 'react-google-recaptcha';
import useFetchUpdateSchedule from '../hooks/useFetchUpdateSchedule';

import H3Title from '../../common/H3Title/H3Title';
import TextInput from '../../common/Input/TextInput/TextInput';
import Button from '../../common/Buttons/Button/Button';

import scheduleStyle from './schedulesModal.module.css';

const reCaptchaKey = "6Le8ugwpAAAAAGo_7BMdYwZ_gZfNGLLXcCqb_TXC";


const SchedulesModal = ({ schedule, onClose, onSave }) => {

    //Etat pour gérer les données des horaires
    const [schedules, setSchedules] = useState(schedule);
    //Etat pour la valeur du captcha
    const [captchaValue, setCaptchaValue] = useState(null);
    //Hook de mise a jour des heures d'ouverture
    const { updateThisSchedule, isLoading } = useFetchUpdateSchedule();

    //Mise à jour de l'état local des horaires lorsque les horaires changes
    useEffect(() => {
        setSchedules(schedule);
    }, [schedule]);

    //Fermeture de la modal
    const closeModal = () => {
      onClose();
    };

    //Suivre les changements dans les champs de saisie du formulaire
    const handleInputChange = (e) => {
      const { name, value } = e.target;
          setSchedules((prevState) => ({
              ...prevState,
              [name]: value,
          }));
      
      }
    
    //soumission du formulaire
    const handleSubmit = async (e) => {
          e.preventDefault();
      //formatage des horaires : ajout des secondes
        const formatTime = (time) => {
          if (!time) return "";
          if (time.length === 5) return `${time}:00`; // Ajoute ":00" pour convertir "HH:mm" en "HH:mm:ss"
          return time;
        };
  
      // Applique le formatage aux heures modifiées
        const updatedSchedule = {
            morningOpening: formatTime(schedules.morningOpening),
            morningClosing: formatTime(schedules.morningClosing),
            afternoonOpening: formatTime(schedules.afternoonOpening),
            afternoonClosing: formatTime(schedules.afternoonClosing),
            idOpeningDay: schedules.idOpeningDay,
            dayOfWeek: schedules.dayOfWeek
        };

      //Ajout de la valeur du captcha au données envoyées au server
        const dataToSend = {
            ...updatedSchedule,
            recaptchaResponse: captchaValue
        }
  
      //Si le captcha est validé, envoi des données au server
        if (captchaValue)  {
            try {

                await updateThisSchedule(schedule.idOpeningDay,  dataToSend);
                onSave(updatedSchedule);
                           
            } catch (error) {
            
            } finally {

              closeModal()
            }
        }
   
    };

    //si chargement affiche la div
    if (isLoading) {

      return <div>Loading...</div>;
    }

    return (
        <div  className={scheduleStyle.modalOverlay}>
            <div className={scheduleStyle.schedulesModal}>
                <H3Title className={scheduleStyle.schedulesModalTitle} h3Text={`Modification des horaires pour ${schedule.dayOfWeek}`}/>
                <form className={scheduleStyle.schedulesForm}>
                    <div className={scheduleStyle.hoursContainer}>
                      <h4 className={scheduleStyle.hoursSectionTitle}>Matin</h4>
                        <TextInput formGroupClass={scheduleStyle.formGroup} inputClassName={scheduleStyle.inputSchedule} label="Ouverture: " name="morningOpening" type="time" value={schedules.morningOpening}  onChange={handleInputChange}/>
                        <TextInput formGroupClass={scheduleStyle.formGroup} inputClassName={scheduleStyle.inputSchedule} label="Fermeture: " name="morningClosing" type="time" value={schedules.morningClosing}  onChange={handleInputChange}/>
                    </div>
                    <div className={scheduleStyle.hoursContainer}>
                    <h4 className={scheduleStyle.hoursSectionTitle}>Après-midi</h4>
                      <TextInput formGroupClass={scheduleStyle.formGroup} inputClassName={scheduleStyle.inputSchedule} label="Ouverture:" name="afternoonOpening" type="time" value={schedules.afternoonOpening}  onChange={handleInputChange}/>
                      <TextInput formGroupClass={scheduleStyle.formGroup} inputClassName={scheduleStyle.inputSchedule} label="Fermeture: " name="afternoonClosing" type="time" value={schedules.afternoonClosing} onChange={handleInputChange}></TextInput>
                    </div>
                    <div className={scheduleStyle.captchaContainer}>
                      <ReCAPTCHA
                          sitekey={reCaptchaKey}
                          onChange={(value) => setCaptchaValue(value)}
                      />
                    </div>

                    <div className={scheduleStyle.scheduleBtnContainer}>
                      <Button type="submit" className={scheduleStyle.inputSchedule} id="saveSchedule" colorStyle="whiteBtn" onClick={handleSubmit}>Sauvegarder</Button>
                      <Button type="button" className={scheduleStyle.inputSchedule} id="cancelSchedule" colorStyle="whiteBtn" onClick={onClose}>Annuler</Button>
                    </div>
              </form>
            </div>          
        </div>
    );
};

export default SchedulesModal;
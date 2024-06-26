import React from 'react';
import Button from '../../common/Buttons/Button/Button';

import cardStyle from './userCard.module.css';

const UserCard = ({ user, onEditUser, onDeleteUser }) => {
  return (
    <div className={`${cardStyle.cardUser} card my-3`}>
            <h5 className={`${cardStyle.cardUserHeader} card-header`}>{user.roleName}</h5>
            <div className={`${cardStyle.cardUserBody} card-body`}>
                <h5 className={`${cardStyle.cardUserTitle} card-title`}>{`${user.lastName} ${user.firstName}`}</h5>
                <p className={`${cardStyle.cardUserContent} card-text`}>{user.userEmail}</p>        
            </div>
            <div className={`${cardStyle.cardContactFooter} card-footer`}>
                <Button onClick={() => onEditUser(user)} className={cardStyle.updateUser} colorStyle="redBtn">Modifier</Button>
                {user.roleName.toLowerCase() !== 'admin' && (
                <Button onClick={() => onDeleteUser(user.idUser)} className={cardStyle.deleteUser} colorStyle="redBtn">Supprimer</Button>
                )}     
            </div>
        </div>
  );
};

export default UserCard;
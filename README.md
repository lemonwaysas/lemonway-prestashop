# Lemonway Prestashop

## configuration de l'API

| Nom | Description |   
| -------- | -------- |  
|Identifiant de l'API |Society  (Login de production de l''API) |
|Mot de passe de l'API|Mot de passe fourni par Lemonway|
|Identifiant du wallet marchand|test-1 (Correspondant au wallet crédité. Vous devez le créer dans le BO Lemonway.)|  
|DIRECTKIT URL|Url de **production** de l'API **DIRECTKIT** fourni par lemonway|
|WEBKIT URL|Url de **production** de l'API **WEBKIT** fourni par lemonway|  
|DIRECTKIT URL TEST|Url de **TEST** de l'API  **DIRECTKIT** fourni par lemonway|
|WEBKIT URL TEST|Url de **TEST** de l'API **WEBKIT** fourni par lemonway|| 
|Activer le mode test|Les appels à l'API se font sur les urls de tests|  


## Configuration de la méthode

| Nom | Description |   
| -------- | -------- | 
|Auto commission |Si la valeur est à NON, vous devez remplir le champs ci-dessous|    
|Montant de la commission|En euro| 
|Activer le Oneclic |Affiche le formulaire Oneclic sur la page des méthodes de paiements| 
|Url du css|https://www.lemonway.fr/mercanet_lw.css|    
  
## Effectuer un virement bancaire      

Lemonway => Virements bancaire => **+** Effectuer un nouveau virement  

| Nom | Description |   
| -------- | -------- | 
|Wallet|Nom du Wallet débiteur|  
|Porteur du wallet|Nom du propiétaire du Wallet|  
|Fonds disponibles |Le montant disponible sur le Wallet débiteur|  
|Statut du wallet|Validité du document expiré|  

## Virement bancaire

| Nom | Description |   
| -------- | -------- | 
|Iban|Iban correspondant au compte débiteur|  
|Montant|Montant à transférer en euro|  


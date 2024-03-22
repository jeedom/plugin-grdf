# GRDF plugin

Plugin allowing data recovery from communicating gas meters _(Gazpar for example)_ via [of a customer account **GRDF**](https://login.monespace.grdf.fr/mire/connexion){:target="\_blank"}.

This plugin is able to provide access to gas consumption readings as well as injection readings if applicable _(professional meters only)_. He uses **the official GRDF ADICT API**.

Depending on the type of meter, 2 types of data can be recovered :

- **Published data** are provided by all meters. This is data transmitted to the supplier to be used for invoicing. This data can be viewed up to a maximum of 5 years ago.
- **Informative data** refer to daily data transmitted by meters publishing monthly data for billing _(1M/MM)_. This data can be consulted up to 3 years ago maximum.

Still depending on the type of meter, the data available is not the same, as is their frequency of availability.

|          **Meter type**           | Published data                                    | Informative data                                  | Relief frequency                                                                                                                                                           | Call frequency                                                                    |
| :-------------------------------: | ------------------------------------------------- | ------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------- |
|      **6M** _(particuliers)_      | Half-year data                                    |                                                   | Every 6 months from D+2 to D+3 after handover                                                                                                                              | 1 to 2 times a month                                                              |
|      **1M** _(particuliers)_      | Monthly data month M-1                            | Daily data :<br>- estimated<br>- final months M-1 | Every month from D+2 to D+3 after the publication date _(published & definitive information)_<br>Every day from D+1 to D+3 _(estimated information)_                       | 1 to 2 times a month _(published)_<br>1 time per day _(informatives)_             |
|    **M.M.** _(professionnels)_    | Monthly data month M-1                            | Daily data month M-1                              | Every month between D+2 after the publication date and the 7th Olympic Games of month M _(published)_<br>Every month between the 10th and 20th of month M _(informatives)_ | 1 to 14 times per month _(published)_<br>1 to 11 times per month _(informatives)_ |
| **Not a word** _(professionnels)_ | Daily data :<br>- estimated<br>- final months M-1 |                                                   | Every day from D+1 to D+2 _(published estimated)_<br>Every month between the 1st and 6th Olympics of month M _(definitively published)_                                    | 1 time per day                                                                    |

> **INFORMATION**
>
> The counters **6M** are supported plugin however they have normally all been replaced by counters **1M** at the end of 2023.

# Configuration

Like any Jeedom plugin, the plugin **GRDF** must be activated after installation.

## Plugin setup

> **INFORMATION**
>
> Feel free to **copy PCE identification number** when it appears during this operation because it will be useful during the Jeedom equipment configuration step.

To begin, you must authorize the sharing of GRDF data with Jeedom by clicking on the image **Authorize access to the GRDF account** from the plugin configuration page :

![Lien espace client GRDF](../images/link_grdf.jpg)

You are then redirected to this page on which you must provide information **your identifiers in the Jeedom market** then click on the button **To validate** :

![Authentification compte Market Jeedom](../images/Auth_Jeedom.jpg)

**Log in to your GRDF customer area** then select a counter **awaiting consent** :

![Sélection compteur GRDF](../images/grdf_home.jpg)

Select your consents then click on the button **To validate** :

- **My gas consumption data** : **Yes** _(obligatoire)_
  - **Start date** : **January 1 of the current year** or January 1st up to 4 years ago to consult previous years in Jeedom.
  - **End date** : Optionally, the same date as the end of consent for example.
  - **Published data** : **Yes** _(obligatoire)_
  - **Informative data** : **Yes** _(strongly recommended)_
- **My contractual data** : **Yes** _(facultatif)_
- **My technical data** : **Yes** _(obligatoire)_
- **Consent start date** : **Today's date**
- **Consent end date** : A choice.

![Consentement GRDF](../images/grdf_choose.jpg)

Once your consents have been validated, you have the possibility to **give your consent for another PCE** Or **return to the Jeedom page** confirming the end of the operation :

![Validation GRDF](../images/grdf_consent.jpg)

> **IMPORTANT**
>
> If you can't access one of these pages, turn off the browser's ad blocker.

## Equipment configuration

To access the different equipment **GRDF**, you have to go to the menu **Plugins → Energy → GRDF**.

> **INFORMATION**
>
> The button **Add** allows you to add a new counter.

Once the access authorizations have been validated, all that remains is to provide **the identification number of the PCE** concerned _(spaces are automatically removed)_ then save the equipment.

The option **Save the conversion coefficient** allows you to note the ratio between the energy actually consumed _(kWh)_ and the volume _(m3)_.

Professional meters _(MM or DD)_ have an additional configuration field allowing you to choose the type of measurement to be taken :

- **Consumption**
- **Injection** _(Biomethane Producers)_
- **The 2**

# Commandes

Orders are created automatically based on the frequency of data collection _(day, month, semester)_ and their type _(definitive or estimated)_.

The plugin is responsible for carrying out monthly and annual calculations when necessary, which is why it is advisable to enter the start date of access to gas consumption data on January 1 during the consent stage.

During the first backup of active and correctly configured equipment, the plugin will automatically integrate the histories available on the GRDF customer area since the start date of access to gas consumption data. This process is likely to last a long time, you can follow its progress from the menu **Analysis → Logs** _(logs in `debug`)_.

> **INFORMATION**
>
> Data is transmitted in kilowatt-hours (`kWh`) with a reading date of 6 a.m. They are not made available in real time but are recorded on their effective date in Jeedom.

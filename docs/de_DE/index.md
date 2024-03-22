# GRDF-Plugin

Plugin zur Datenwiederherstellung von kommunizierenden Gaszählern _(Gazpar zum Beispiel)_ über [eines Kundenkontos **GRDF**](https://login.monespace.grdf.fr/mire/connexion){:target="\_blank"}.

Dieses Plugin ermöglicht den Zugriff auf Gasverbrauchswerte sowie ggf. Einspritzwerte _(Nur professionelle Messgeräte)_. Er benutzt **die offizielle GRDF ADICT API**.

Abhängig vom Zählertyp können zwei Arten von Daten wiederhergestellt werden :

- **Veröffentlichte Daten** werden von allen Zählern bereitgestellt. Hierbei handelt es sich um an den Lieferanten übermittelte Daten, die für die Rechnungsstellung verwendet werden. Diese Daten können bis zu einem Zeitraum von maximal 5 Jahren eingesehen werden.
- **Aufschlussreiche Daten** beziehen sich auf die täglichen Daten, die von den Zählern übermittelt werden und monatliche Daten für die Abrechnung veröffentlichen _(1M/MM)_. Diese Daten können bis zu einem Zeitraum von maximal 3 Jahren eingesehen werden.

Je nach Zählertyp sind die verfügbaren Daten jedoch unterschiedlich, ebenso wie die Verfügbarkeitshäufigkeit.

|          **Zählertyp**           | Veröffentlichte Daten                                  | Aufschlussreiche Daten                                 | Entlastungshäufigkeit                                                                                                                                                               | Anrufhäufigkeit                                                                      |
| :------------------------------: | ------------------------------------------------------ | ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
|     **6M** _(particuliers)_      | Halbjahresdaten                                        |                                                        | Alle 6 Monate von D+2 bis D+3 nach Übergabe                                                                                                                                         | 1 bis 2 Mal im Monat                                                                 |
|     **1M** _(particuliers)_      | Monatliche Daten Monat M-1                             | Tägliche Daten :<br>- geschätzt<br>- letzte Monate M-1 | Jeden Monat von D+2 bis D+3 nach dem Erscheinungsdatum _(veröffentlichte und endgültige Informationen)_<br>Täglich von D+1 bis D+3 _(geschätzte Informationen)_                     | 1 bis 2 Mal im Monat _(veröffentlicht)_<br>1 Mal pro Tag _(informatives)_            |
|   **M.M.** _(professionnels)_    | Monatliche Daten Monat M-1                             | Täglicher Datenmonat M-1                               | Jeden Monat zwischen D+2 nach dem Veröffentlichungsdatum und der 7. Olympiade des Monats M _(veröffentlicht)_<br>Jeden Monat zwischen dem 10. und 20. des Monats M _(informatives)_ | 1 bis 14 Mal pro Monat _(veröffentlicht)_<br>1 bis 11 Mal pro Monat _(informatives)_ |
| **Kein Wort** _(professionnels)_ | Tägliche Daten :<br>- geschätzt<br>- letzte Monate M-1 |                                                        | Täglich von D+1 bis D+2 _(geschätzt veröffentlicht)_<br>Jeden Monat zwischen der 1. und 6. Olympiade des Monats M _(endgültig veröffentlicht)_                                      | 1 Mal pro Tag                                                                        |

> **INFORMATION**
>
> Die Zähler **6M** sind unterstützte Plugins, wurden jedoch normalerweise alle durch Zähler ersetzt **1M** Ende 2023.

# Configuration

Wie jedes Jeedom-Plugin ist das Plugin **GRDF** muss nach der Installation aktiviert werden.

## Plugin-Setup

> **INFORMATION**
>
> Fühlen sich frei **PCE-Identifikationsnummer kopieren** wenn es während dieses Vorgangs erscheint, da es während des Jeedom-Gerätekonfigurationsschritts nützlich sein wird.

Zunächst müssen Sie die Weitergabe von GRDF-Daten an Jeedom autorisieren, indem Sie auf das Bild klicken **Autorisieren Sie den Zugriff auf das GRDF-Konto** von der Plugin-Konfigurationsseite :

![Lien espace client GRDF](../images/link_grdf.jpg)

Anschließend werden Sie auf diese Seite weitergeleitet, auf der Sie Angaben machen müssen **Ihre Identifikatoren auf dem Jeedom-Markt** Klicken Sie dann auf die Schaltfläche **Bestätigen** :

![Authentification compte Market Jeedom](../images/Auth_Jeedom.jpg)

**Melden Sie sich in Ihrem GRDF-Kundenbereich an** Wählen Sie dann einen Zähler aus **warten auf Zustimmung** :

![Sélection compteur GRDF](../images/grdf_home.jpg)

Wählen Sie Ihre Einwilligungen aus und klicken Sie dann auf die Schaltfläche **Bestätigen** :

- **Meine Gasverbrauchsdaten** : **Ja** _(obligatoire)_
  - **Startdatum** : **1. Januar des laufenden Jahres** oder am 1. Januar bis zu 4 Jahren, um frühere Jahre in Jeedom zu konsultieren.
  - **Endtermin** : Optional das gleiche Datum wie z. B. das Ende der Einwilligung.
  - **Veröffentlichte Daten** : **Ja** _(obligatoire)_
  - **Aufschlussreiche Daten** : **Ja** _(wärmstens empfohlen)_
- **Meine Vertragsdaten** : **Ja** _(facultatif)_
- **Meine technischen Daten** : **Ja** _(obligatoire)_
- **Beginndatum der Einwilligung** : **Heutiges Datum**
- **Enddatum der Einwilligung** : Eine Wahl.

![Consentement GRDF](../images/grdf_choose.jpg)

Sobald Ihre Einwilligungen validiert wurden, haben Sie die Möglichkeit dazu **Geben Sie Ihr Einverständnis für eine weitere PCE** Oder **Zurück zur Jeedom-Seite** Bestätigung des Endes des Vorgangs :

![Validation GRDF](../images/grdf_consent.jpg)

> **WICHTIG**
>
> Wenn Sie auf eine dieser Seiten nicht zugreifen können, deaktivieren Sie den Werbeblocker des Browsers.

## Gerätekonfiguration

Um auf die verschiedenen Geräte zuzugreifen **GRDF**, Sie müssen zum Menü gehen **Plugins → Energie → GRDF**.

> **INFORMATION**
>
> Die Taste **Hinzufügen** ermöglicht Ihnen das Hinzufügen eines neuen Zählers.

Nach der Validierung der Zugangsberechtigungen bleibt nur noch die Bereitstellung **die Identifikationsnummer des PCE** betroffen _(Leerzeichen werden automatisch entfernt)_ Speichern Sie dann die Ausrüstung.

Die Option **Speichern Sie den Umrechnungskoeffizienten** ermöglicht es Ihnen, das Verhältnis zwischen der tatsächlich verbrauchten Energie zu ermitteln _(kWh)_ und die Lautstärke _(m3)_.

Professionelle Messgeräte _(MM oder TT)_ verfügen über ein zusätzliches Konfigurationsfeld, in dem Sie die Art der durchzuführenden Messung auswählen können :

- **Verbrauch**
- **Injektion** _(Biomethan-Produzenten)_
- **Die 2**

# Commandes

Bestellungen werden automatisch basierend auf der Häufigkeit der Datenerfassung erstellt _(Tag, Monat, Semester)_ und ihre Art _(endgültig oder geschätzt)_.

Das Plugin ist dafür verantwortlich, bei Bedarf monatliche und jährliche Berechnungen durchzuführen, weshalb es ratsam ist, in der Einwilligungsphase das Startdatum des Zugriffs auf die Gasverbrauchsdaten auf den 1. Januar einzutragen.

Bei der ersten Sicherung aktiver und korrekt konfigurierter Geräte integriert das Plugin automatisch die im GRDF-Kundenbereich verfügbaren Historien seit dem Startdatum des Zugriffs auf Gasverbrauchsdaten. Dieser Vorgang wird wahrscheinlich lange dauern. Sie können den Fortschritt über das Menü verfolgen **Analyse → Protokolle** _(meldet sich bei „debug“ an)_.

> **INFORMATION**
>
> Die Datenübermittlung erfolgt in Kilowattstunden („kWh“) mit Ablesedatum 6 Uhr morgens. Sie werden nicht in Echtzeit zur Verfügung gestellt, sondern zum Zeitpunkt ihres Inkrafttretens in Jeedom erfasst.

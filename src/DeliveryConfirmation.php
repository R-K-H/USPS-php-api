<?php

namespace USPS;

/**
 * Class PriorityLabel
 *
 * @package USPS
 */
class DeliveryConfirmation extends USPSBase
{
    /**
     * @var string - the api version used for this type of call
     */
    protected $apiVersion = 'DeliveryConfirmationV4';
    /**
     * @var array - route added so far.
     */
    protected $fields = [];

    /**
     * Perform the API call.
     *
     * @return string
     */
    public function createLabel() 
    {
        // Add missing required
        $this->addMissingRequired();

        // Sort them
        // Hack by the only way this will work properly
        // since usps wants the tags to be in 
        // a certain order
        ksort($this->fields, SORT_NUMERIC);

        // remove the \d. from the key
        foreach($this->fields as $key => $value) {
            $newKey = str_replace('.', '', $key);
            $newKey = preg_replace('/\d+\:/', '', $newKey);
            unset($this->fields[$key]);
            $this->fields[$newKey] = $value;
        }

        return $this->doRequest();
    }

    /**
     * Return the USPS confirmation/tracking number if we have one
     * @return string|bool
     */
    public function getConfirmationNumber() {
      $response = $this->getArrayResponse();
      // Check to make sure we have it
      if(isset($response[$this->getResponseApiName()])) {
        if(isset($response[$this->getResponseApiName()]['DeliveryConfirmationNumber'])) {
          return $response[$this->getResponseApiName()]['DeliveryConfirmationNumber'];
        }
      }

      return false;
    }

    /**
     * Return the USPS label as a base64 encoded string
     * @return string|bool
     */
    public function getLabelContents() {
      $response = $this->getArrayResponse();
      // Check to make sure we have it
      if(isset($response[$this->getResponseApiName()])) {
        if(isset($response[$this->getResponseApiName()]['DeliveryConfirmationLabel'])) {
          return $response[$this->getResponseApiName()]['DeliveryConfirmationLabel'];
        }
      }

      return false;
    }

    /**
     * Return the USPS receipt as a base64 encoded string
     * @return string|bool
     */
    public function getReceiptContents() {
      $response = $this->getArrayResponse();
      // Check to make sure we have it
      if(isset($response[$this->getResponseApiName()])) {
        if(isset($response[$this->getResponseApiName()]['EMReceipt'])) {
          return $response[$this->getResponseApiName()]['EMReceipt'];
        }
      }

      return false;
    }

    /**
     * returns array of all fields added
     * @return array
     */
    public function getPostFields() {
      return $this->fields;
    }

    /**
     * Set the from address
     * @param string $firstName
     * @param string $lastName
     * @param string $company
     * @param string $address
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $address2
     * @param string $zip4
     * @param string $phone
     * @return object
     */
    public function setFromAddress($firstName, $lastName, $company, $address, $city, $state, $zip, $address2=null, $zip4=null, $phone=null, $email=null) {
      $this->setField(3, 'FromName', $firstName .' '. $lastName);
      $this->setField(4, 'FromFirm', $company);
      $this->setField(5, 'FromAddress1', $address2);
      $this->setField(6, 'FromAddress2', $address);
      $this->setField(7, 'FromCity', $city);
      $this->setField(8, 'FromState', $state);
      $this->setField(9, 'FromZip5', $zip);
      $this->setField(10, 'FromZip4', $zip4);
      //$this->setField(14, 'FromPhone', $phone);
      $this->setField(33, 'SenderEMail', $email);

      return $this;
    }

    /**
     * Set the to address
     * @param string $firstName
     * @param string $lastName
     * @param string $company
     * @param string $address
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $address2
     * @param string $zip4
     * @param string $phone
     * @return object
     */
    public function setToAddress($firstName, $lastName, $company, $address, $city, $state, $zip, $address2=null, $zip4=null, $phone=null, $email=null) {
      $this->setField(11, 'ToName', "ATT: Collection");
      //$this->setField(11.1, 'ToFirstName', $firstName);
      //$this->setField(11.2, 'ToLastName', $lastName);
      $this->setField(12, 'ToFirm', $company);
      $this->setField(13, 'ToAddress1', $address2);
      $this->setField(14, 'ToAddress2', $address);
      $this->setField(15, 'ToCity', $city);
      $this->setField(16, 'ToState', $state);
      $this->setField(17, 'ToZip5', $zip);
      $this->setField(18, 'ToZip4', $zip4);
      //$this->setField(24, 'ToPhone', $phone);
      //$this->setField(22, 'ToContactEMail', $email);
      $this->setField(35, 'RecipientEMail', $email);

      return $this;
    }

    /**
    * Set ship date
    *
    */
    public function setShipDate($date) {
      $this->setField(29, 'LabelDate', $date);
    }

    /**
     * Set package weight in ounces
     *
     */
    public function setWeightOunces($weight) {
      $this->setField(19, 'WeightInOunces', $weight);
      return $this;
    }

    /** 
     * Set any other requried string make sure you set the correct position as well
     * as the position of the items matters
     * @param int $position 
     * @param string $key
     * @param string $value
     * @return object
     */
    public function setField($position, $key, $value) {
      $this->fields[$position . ':' . $key] = $value;
      return $this;
    }

    /**
     * Add missing required elements
     * @return void
     */
    protected function addMissingRequired() {
      $required = array(
        '1:Option' => '',
        '1.1:Revision' => '2',
        '2:ImageParameters' => '',
        //'2.1:ImageParameter' =>'',
        //
        //'19:ToPOBoxFlag' => '',
        //'20:ToContactPreference' => 'EMAIL',
        //'21:ToContactMessaging' => '',
        //22: ToContactEMail
        // 23:Wieght
        '23:ServiceType' => 'PRIORITY', /*PRIORITY
  FIRST CLASS
  STANDARD POST
  MEDIA MAIL
  LIBRARY MAIL  */
  //First-Class Mail® ParcelMore info about First-Class Mail® Parcel Thu, Dec 10$2.74Not available
  /*Priority Mail 1-Day™ Small Flat Rate BoxMore info about Priority Mail 1-Day™ Small Flat Rate Box
  USPS-Produced Box: 8-5/8" x 5-3/8" x 1-5/8"
  Wed, Dec 9
  $5.95
  $5.25*/
        //'24:InsuredAmount' => '50.00',
        '25:WaiverOfSignature' => 'False',
        // '30:NoHoliday' => '',
        // '31:NoWeekend' => '',
        '26:SeparateReceiptPage' => 'False',
        '27:POZipCode' => '',
        // '34:FacilityType' => 'DDU',
        '28:ImageType' => 'PDF',
        //29:LabelDate
        '30:CustomerRefNo' => '',
        '31:AddressServiceRequested' => 'False',
        '32:SenderName' => '',
        //33:sender email
        '34:RecipientName' => '',
        //35 recipient email
        '36:AllowNonCleansedDestAddr' => 'Y',
        '37:HoldForManifest' => 'N',
        '38:Container' => 'SM Flat Rate Box',
        // 
        // '44:InsuredAmount' => '',
        '39:Size' => 'Regular',
        '40:Width' => '',
        '41:Length' => '',
        '42:Height' => '',
        '43:Girth' => '',
        '44:Machinable' => 'True',
        //'45:CommercialPrice' => 'false',
        //'46:ExtraServices' => '',
        //'47:ExtraService' => '',
        // '48:CarrierRelease' => 'A',
        // '49:ReturnCommitments' => '',
        // '50:GroundOnly' => '',
        // '51:Content' => '',
        // '52:ContentType' => '',
        // '53:ContentDescription' => '',
      );
      foreach($required as $item => $value) {
        $explode = explode(':', $item);
        if(!isset($this->fields[$item])) {
          $this->setField($explode[0], $explode[1], $value);
        }
      }
    }

}

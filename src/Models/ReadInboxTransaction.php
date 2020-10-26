<?php

namespace UpnQr\Models;

use Webklex\PHPIMAP\ClientManager;

class ReadInboxTransaction {
    private $client;

    // Save all parsed transactions.
    private $bonusTransactions;

    public function __construct($config) {
        $this->client = new ClientManager();
        $this->client = $this->client->make([
            'host'          => $config["host"],
            'port'          => $config["port"],
            'validate_cert' => true,
            'encryption'    => 'ssl',
            'username'      => $config["username"],
            'password'      => $config["password"],
            'protocol'      => 'imap'
        ]);


        $this->bonusTransactions = [];
    }

    /**
     * Read transactions from inbox.
     *
     * @return BankTransaction[]
     */
    public function read(){
        $this->client->connect();

        $folders = $this->client->getFolders();

//        $myfile = fopen("log.txt", "w");

        // Go through each mail folder.
        foreach($folders as $folder){

            // Retrieve unseen messages.
            $messages = $folder->query()->unseen()->leaveUnread()->get();

            foreach($messages as $message){

                $attachments = $message->getAttachments();

                // Go through xml attachments.
                foreach($attachments as $attachment){
                    if (strpos($attachment->getMimeType(), 'xml') == false)
                        continue;

                    $xml = simplexml_load_string($attachment->getContent());

                    // Go through each transaction.
                    foreach ($xml->BkToCstmrStmt->Stmt->Ntry as $row){

                        //  Paid by physical (regular) person.
                        if (!isset($row->NtryDtls->TxDtls->RltdPties->Dbtr))
                            continue;

                        // Reference does not exist
                        if (!isset($row->NtryDtls->TxDtls->RmtInf->Strd->CdtrRefInf->Ref))
                            continue;


                        // Retrieve name.
                        $name = $row->NtryDtls->TxDtls->RltdPties->Dbtr->Nm;

                        // Retrieve reference.
                        $reference = $row->NtryDtls->TxDtls->RmtInf->Strd->CdtrRefInf->Ref;

                        // Retrieve amount.
                        $amount = $row->Amt;

                        // Add transaction data to list.
                        $transaction = new BankTransaction($name, $reference, $amount);
                        $this->bonusTransactions[] = $transaction;
                    }
                }

                // After successfull read mark message as seen.
//                $message->setFlag(['Seen']);
            }
        }

//        fclose($myfile);

        return $this->getBonusTransactions();
    }


    /**
     * Get saved transactions.
     *
     * @return BankTransaction[]
     */
    public function getBonusTransactions(){
        return $this->bonusTransactions;
    }
}

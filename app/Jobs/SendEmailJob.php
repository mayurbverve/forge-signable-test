<?php
  
namespace App\Jobs;
   
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SendEmail;
use Mail;
use Log;
   
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  
    protected $template;
    protected $to;
    protected $data;
    protected $attachments;
    protected $cc;
  
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($template, $to, $data, $attachments, $cc)
    {
        $this->template = $template;
        $this->to = $to;
        $this->data = $data;
        $this->attachments = $attachments;
        $this->cc = $cc;
    }
   
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){   
        $template = $this->template;
        $to = $this->to;
        $data = $this->data;
        $attachments = $this->attachments;
        $cc = $this->cc;

        Log::info("call mail send function : In function", ["data"=>$data,"to"=>$to,'subject' => $template->subject]);
        
        $email_body = $template->body;
        if(!empty($data)){
            foreach ($data as $key => $value) {
                $temp_key = "{{".$key."}}";
                $email_body = str_replace($temp_key, $value, $email_body); 
            }
        }                

        $email = new SendEmail($template->subject, $email_body);
        $message = Mail::to($to);
     
        //Add cc recipients
        if ($cc && !empty($cc)) {
            $message->cc($cc);                    
        }

        // Add attachments if available
        if ($this->attachments && !empty($this->attachments[0])) {
            foreach ($this->attachments as $attachment) {
                $message->attach($attachment);
            }
        }

        $message->send($email);

    }
}
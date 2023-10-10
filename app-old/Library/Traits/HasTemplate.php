<?php

namespace Acelle\Library\Traits;

use Acelle\Model\Template;
use Exception;

trait HasTemplate
{

    /**
     * Campaign has one template.
     */
    public function template()
    {
        return $this->belongsTo('Acelle\Model\Template');
    }

    /**
     * Get template.
     */
    public function setTemplate($template)
    {
        $campaignTemplate = $template->copy([
            'name' => trans('messages.campaign.template_name', ['name' => $this->name]),
            'customer_id' => $this->customer_id,
        ]);

        // remove exist template
        if ($this->template) {
            $this->template->deleteAndCleanup();
        }

        $this->template_id = $campaignTemplate->id;
        $this->save();
        $this->refresh();
        $this->updatePlainFromHtml();
        $this->updateLinks();
    }

    /**
     * Upload a template.
     */
    public function uploadTemplate($request)
    {
        $template = Template::uploadTemplate($request);
        $this->setTemplate($template);
    }

    /**
     * Check if email has template.
     */
    public function hasTemplate()
    {
        return $this->template()->exists();
    }

    /**
     * Get thumb.
     */
    public function getThumbUrl()
    {
        if ($this->template) {
            return $this->template->getThumbUrl();
        } else {
            return url('assets/images/placeholder.jpg');
        }
    }

    /**
     * Remove email template.
     */
    public function removeTemplate()
    {
        $this->template->deleteAndCleanup();
    }

    /**
     * Update campaign plain text.
     */
    public function updatePlainFromHtml()
    {
        if (!$this->plain) {
            $this->plain = preg_replace('/\s+/', ' ', preg_replace('/\r\n/', ' ', strip_tags($this->getTemplateContent())));
            $this->save();
        }
    }

    /**
     * Set template content.
     */
    public function setTemplateContent($content)
    {
        if (!$this->template) {
            throw new Exception('Cannot set content: campaign/email does not have template!');
        }

        $template = $this->template;
        $template->content = $content;
        $template->save();
    }

    /**
     * Get template content.
     */
    public function getTemplateContent()
    {
        if (!$this->template) {
            throw new Exception('Cannot get content: campaign/email does not have template!');
        }

        return $this->template->content;
    }
}

<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}?><div class="modal fade" id="createRuleModal" tabindex="-1" aria-labelledby="createRuleModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="createRuleModalLabel">Create Rule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="createRuleForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="bbcs_text_input">
                            <div class="bbcs_label_input_box">
                                <span class="bbcs_label_input">Type</span>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Select the type of rule to apply"></i>
                            </div>
                            <div class="bbcs_text_input_inner">
                                <select class="form-select form-control" id="type" name="type" required>
                                    <option value="referer">Referer</option>
                                    <option value="useragent">User Agent</option>
                                    <option value="lang">Language</option>
                                    <option value="asname">AS Name</option>
                                    <option value="ptr">PTR</option>
                                    <option value="country">Country</option>
                                    <option value="page">Page</option>
                                    <option value="recaptcha">reCAPTCHA</option>
                                    <option value="adblock">Adblock</option>
                                    <option value="hosting">Hosting</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bbcs_text_input">
                            <div class="bbcs_label_input_box">
                                <span class="bbcs_label_input">Priority</span>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Set the priority of the rule (1-100)"></i>
                            </div>
                            <div class="bbcs_text_input_inner">
                                <input type="range" class="form-range" id="priority" name="priority" min="1" max="100" required>
                                <output for="priority" id="priorityValue"></output>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="bbcs_text_input">
                            <div class="bbcs_label_input_box">
                                <span class="bbcs_label_input">Data</span>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Enter the data for the rule"></i>
                            </div>
                            <div class="bbcs_text_input_inner">
                                <textarea class="form-control" id="data" name="data" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bbcs_text_input">
                            <div class="bbcs_label_input_box">
                                <span class="bbcs_label_input">Comment</span>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Add a comment for this rule"></i>
                            </div>
                            <div class="bbcs_text_input_inner">
                                <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="bbcs_text_input">
                            <div class="bbcs_label_input_box">
                                <span class="bbcs_label_input">Rule</span>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Select the action for this rule"></i>
                            </div>
                            <div class="bbcs_text_input_inner">
                                <select class="form-select form-control" id="rule" name="rule" required>
                                    <option value="allow">Allow</option>
                                    <option value="gray">Gray</option>
                                    <option value="dark">Dark</option>
                                    <option value="block">Block</option>
                                    <option value="permanently_ban">Permanently Ban</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bbcs_text_input">
                            <div class="bbcs_label_input_box">
                                <span class="bbcs_label_input">Expires</span>
                                <i class="fa-regular fa-circle-question" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Set the expiration date and time for this rule"></i>
                            </div>
                            <div class="bbcs_text_input_inner">
                                <input type="datetime-local" class="form-control" id="expires" name="expires" required>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" form="createRuleForm" class="btn btn-primary">Save changes</button>
        </div>
    </div>
</div>
</div>
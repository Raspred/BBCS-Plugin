<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}?><div class="modal fade" id="addIPv6Modal" tabindex="-1" aria-labelledby="addIPv6ModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addIPv6ModalLabel">Add IPv6 rule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="addIPv6Form">
                <div class="mb-3">
                    <label for="addPriority" class="form-label">Priority</label>
                    <input type="number" class="form-control" id="addPriority" name="priority" min="1" max="100" required>
                </div>
                <div class="mb-3">
                    <label for="addIp" class="form-label">IP Address</label>
                    <input type="text" class="form-control" id="addIp" name="ip" required>
                </div>
                <div class="mb-3">
                    <label for="addRule" class="form-label">Rule</label>
                    <select class="form-select" id="addRule" name="rule" required>
                        <option value="allow">Allow</option>
                        <option value="block">Block</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="addExpires" class="form-label">Expires</label>
                    <input type="datetime-local" class="form-control" id="addExpires" name="expires" required>
                </div>
                <div class="mb-3">
                    <label for="addComment" class="form-label">Comment</label>
                    <textarea class="form-control" id="addComment" name="comment" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add</button>
            </form>
        </div>
    </div>
</div>
</div>
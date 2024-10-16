<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}?><div class="modal fade" id="editIPv4Modal" tabindex="-1" aria-labelledby="editIPv4ModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="editIPv4ModalLabel">Edit IPv4 rule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="editIPv4Form">
                <input type="hidden" name="id">
                <div class="mb-3">
                    <label for="priority" class="form-label">Priority</label>
                    <input type="number" class="form-control" id="priority" name="priority" min="1" max="100" required>
                </div>
                <div class="mb-3">
                    <label for="ip" class="form-label">IP Address</label>
                    <input type="text" class="form-control" id="ip" name="ip" required>
                </div>
                <div class="mb-3">
                    <label for="rule" class="form-label">Rule</label>
                    <select class="form-select" id="rule" name="rule" required>
                        <option value="allow">Allow</option>
                        <option value="block">Block</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="expires" class="form-label">Expires</label>
                    <input type="datetime-local" class="form-control" id="expires" name="expires" required>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Comment</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
</div>
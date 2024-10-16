<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}?><div class="modal fade" id="createPathModal" tabindex="-1" aria-labelledby="createPathModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="createPathModalLabel">Create Path</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="createPathForm">
                <div class="mb-3">
                    <label for="priority" class="form-label">Priority</label>
                    <input type="range" class="form-range" id="priority" name="priority" min="1" max="100" required>
                    <output for="priority" id="priorityValue"></output>
                </div>
                <div class="mb-3">
                    <label for="search" class="form-label">Search</label>
                    <textarea class="form-control" id="search" name="search" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="rule" class="form-label">Rule</label>
                    <select class="form-select" id="rule" name="rule" required>
                        <option value="allow">Allow</option>
                        <option value="gray">Gray</option>
                        <option value="dark">Dark</option>
                        <option value="block">Block</option>
                        <option value="permanently_ban">Permanently Ban</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Comment</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" form="createPathForm" class="btn btn-primary">Save</button>
        </div>
    </div>
</div>
</div>

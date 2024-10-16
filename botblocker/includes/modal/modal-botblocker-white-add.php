<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}?><div class="modal fade" id="createWhiteModal" tabindex="-1" aria-labelledby="createWhiteModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="createWhiteModalLabel">Create White Bot</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="createWhiteForm">
                <div class="mb-3">
                    <label for="priority" class="form-label">Priority</label>
                    <input type="range" class="form-range" id="priority" name="priority" min="1" max="100" required>
                    <output for="priority" id="priorityValue"></output>
                </div>
                <div class="mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" required>
                </div>
                <div class="mb-3">
                    <label for="data" class="form-label">Data</label>
                    <textarea class="form-control" id="data" name="data" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="rule" class="form-label">Rule</label>
                    <select class="form-select" id="rule" name="rule" required>
                        <option value="allow">Allow</option>
                        <option value="gray">Gray</option>
                        <option value="dark">Dark</option>
                        <option value="block">Block</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Comment</label>
                    <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="distance" class="form-label">Distance</label>
                    <input type="text" class="form-control" id="distance" name="distance">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" form="createWhiteForm" class="btn btn-primary">Save</button>
        </div>
    </div>
</div>
</div>
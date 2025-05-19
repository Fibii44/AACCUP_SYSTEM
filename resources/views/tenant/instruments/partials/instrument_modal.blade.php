<!-- Instrument Modal -->
<div class="modal fade" id="instrumentModal" tabindex="-1" aria-labelledby="instrumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="instrumentModalLabel">Add Instrument</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="instrument-form" data-edit="false">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="instrument-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="instrument-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="instrument-description" class="form-label">Description</label>
                        <textarea class="form-control" id="instrument-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="instrument-order" class="form-label">Order</label>
                        <input type="number" class="form-control" id="instrument-order" name="order" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div> 
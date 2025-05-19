<!-- Indicator Modal -->
<div class="modal fade" id="indicatorModal" tabindex="-1" aria-labelledby="indicatorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="indicatorModalLabel">Add Indicator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="indicator-form" data-edit="false">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="indicator-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="indicator-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="indicator-description" class="form-label">Description</label>
                        <textarea class="form-control" id="indicator-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="indicator-order" class="form-label">Order</label>
                        <input type="number" class="form-control" id="indicator-order" name="order" value="0" min="0">
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
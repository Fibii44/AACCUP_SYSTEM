<!-- Area Modal -->
<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="areaModalLabel">Add Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="area-form" data-edit="false">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="area-name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="area-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="area-description" class="form-label">Description</label>
                        <textarea class="form-control" id="area-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="area-order" class="form-label">Order</label>
                        <input type="number" class="form-control" id="area-order" name="order" value="0" min="0">
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
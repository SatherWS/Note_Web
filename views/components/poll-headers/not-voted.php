<div>
    <label>Username:</label>
    <input type='text' value='<?php echo getenv('username');?>' name='usr' class='form-control' placeholder='Enter Your Name'>
</div>
<div>
    <label>Cast Your Vote Here:</label>
    <br>
    <button type="submit" name="ballot" value="yes" class="vote-btn">Yes</button>
    <button type="submit" name="ballot" value="no" class="vote-btn">No</button>
    <button type="submit" name="ballot" value="maybe" class="vote-btn">Maybe</button>
</div>
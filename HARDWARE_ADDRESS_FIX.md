# Hardware Address Fix - August 23, 2026

## Issue Found
The hardware addresses for HORN and CTRLROOM were swapped in the database, causing incorrect parent-child relationships.

## Original (Incorrect) Addresses
- CTRLROOM: `10-4` ❌
- HORN: `11-4` ❌

This caused all child rooms to point to HORN as their parent instead of CTRLROOM.

## Corrected Addresses
- CTRLROOM: `11-4` ✅ (parent for all indoor rooms)
- HORN: `10-4` ✅ (standalone outdoor speaker)

## What Was Fixed

### 1. Hardware Address Swap
Swapped the hardware addresses in the database to match the real-world system:
```php
// CTRLROOM: 10-4 → 11-4
// HORN: 11-4 → 10-4
```

### 2. Updated testType() Method
**File**: `app/Http/Controllers/HardwareController.php`

**Previous Issue**:
- Used `whereNotNull('speaker_zone_id')` which excluded parent rooms
- Didn't implement parent-child activation logic
- Created old-style commands

**Fix Applied**:
- Changed to `whereNotNull('hardware_address')`
- Implemented parent-child activation logic:
  - For parent types (HORN, CTRLROOM): Direct activation
  - For child types: Activate parents first, then children after 2s delay
- Uses correct command types: `activate_parent` and `test_speaker`

## Parent-Child Relationship

### CTRLROOM (11-4)
- Parent for ALL indoor rooms (CLASS, OFFICE, etc.)
- Must be activated BEFORE any child room can work
- Example: To activate 7C (9-1), must first activate CTRLROOM (11-4)

### HORN (10-4)
- Standalone outdoor speaker
- No children (acts as its own parent)

### Child Rooms
All rooms with `parent_hardware_address = '11-4'` require CTRLROOM to be activated first:
- 7A, 7B, 7C, 7D (hardware addresses: 9-1, 9-2, 9-3, 9-4)
- 8A, 8B, 8C, 8D
- 9A, 9B, 9C, 9D
- And all other indoor rooms

## Activation Sequence

### Manual Room Test (testRoom)
1. Activate parent (CTRLROOM) - immediate
2. Activate child room - 1 second delay

### Type-Based Test (testType) - **NEWLY FIXED**
For parent types:
1. Activate parent directly

For child types:
1. Activate required parents - immediate
2. Activate all child rooms - 2 second delay

### ON ALL (testAllZones)
1. Activate all parents (HORN, CTRLROOM) - immediate
2. Activate all children - 2 second delay

### Bell Schedule (CheckBellSchedule)
1a. Activate parents - immediate
1b. Activate children - 2 second delay
2. Play audio - 4 second delay
3a. Deactivate children - after audio ends
3b. Deactivate parents - 1 second after children

## Verification

After fix, verified:
- Room 7C (9-1) parent_hardware_address: `11-4`
- Parent room for 7C: CTRLROOM (11-4) ✅
- CTRLROOM is recognized as parent: YES ✅
- Room 7C requires parent: YES ✅

## Files Modified
1. `app/Http/Controllers/HardwareController.php` - testType() method
2. Database: Updated hardware_address for HORN and CTRLROOM
3. `app/Models/Room.php` - Already has correct isParent() logic checking for both 10-4 and 11-4

## Testing Recommendations
1. Test CTRLROOM button activation (should work now)
2. Test HORN button activation (should work)
3. Test individual room buttons (should activate CTRLROOM first)
4. Test ON ALL button (should activate both parents + all children)
5. Test bell schedule (should follow 5-step sequence)

## Next Steps
The system is now correctly configured for the parent-child hardware architecture. The Python Bridge should receive commands in the correct order with proper timing delays.

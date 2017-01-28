using System;
using Famoser.FrameworkEssentials.Models;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Models
{
    public class SyncActionInformation : PropertyChangedModel, ISyncActionInformation
    {
        public SyncActionInformation(SyncAction action)
        {
            Action = action;
        }

        public void SetSyncActionResult(SyncActionError result)
        {
            SyncActionError = result;
            IsSuccessful = result == SyncActionError.None;
            IsCompleted = true;
        }

        public void SetSyncActionException(Exception exception)
        {
            SyncActionError = SyncActionError.ExecutionFailed;
            IsSuccessful = false;
            IsCompleted = true;
        }

        private bool _isCompleted;
        public bool IsCompleted
        {
            get { return _isCompleted; }
            set { Set(ref _isCompleted, value); }
        }

        private bool _isSuccessful;
        public bool IsSuccessful
        {
            get { return _isSuccessful; }
            set { Set(ref _isSuccessful, value); }
        }

        public SyncActionError SyncActionError { get; private set; }

        public SyncAction Action { get; }
    }
}

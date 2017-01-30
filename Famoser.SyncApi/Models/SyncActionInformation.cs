using System;
using Famoser.FrameworkEssentials.Models;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Models
{
    public class SyncActionInformation : PropertyChangedModel, ISyncActionInformation
    {
        public SyncActionInformation(SyncAction syncAction)
        {
            SyncAction = syncAction;
        }

        public void SetSyncActionResult(SyncActionError result)
        {
            SyncActionError = result;
            IsSuccessful = result == SyncActionError.None;
            IsCompleted = true;

            RaisePropertyChanged(() => FullDescription);
        }

        public void SetSyncActionException(Exception exception)
        {
            SyncActionError = SyncActionError.ExecutionFailed;
            IsSuccessful = false;
            IsCompleted = true;

            RaisePropertyChanged(() => FullDescription);
        }

        public string FullDescription
        {
            get
            {
                if (IsCompleted)
                {
                    if (IsSuccessful)
                    {
                        return EnumHelper.EnumToString(SyncAction) + " done";
                    }
                    return EnumHelper.EnumToString(SyncAction) + " failed (" + EnumHelper.EnumToString(SyncActionError) + ")";
                }
                return EnumHelper.EnumToString(SyncAction) + " ...";
            }
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

        public SyncAction SyncAction { get; }
    }
}
